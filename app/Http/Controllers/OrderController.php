<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(){
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Admin")){
            $orders = Order::with([
                'user' => function($query){
                    $query->select(['id', 'name', 'surname', 'patronymic', 'date_of_birth', 'email', 'phone']);
                },
                'address' => function($query){
                    $query->select(['id', 'city', 'street', 'house_number', "apartment_number", 'entrance', 'floor', 'intercom', 'gate', 'comment']);
                },
                'products',
                'paymentMethod' => function ($query) {
                    $query->select(['id', 'name']); 
                },
                'deliveryMethod' => function ($query) {
                    $query->select(['id', 'name']);
            },
            ])->get();
        }
        elseif ($user->hasRole("Client")){   
            $orders = Order::with([
                'user' => function($query){
                    $query->select(['id', 'name', 'surname', 'patronymic', 'date_of_birth', 'email', 'phone']);
                },
                'address' => function($query){
                    $query->select(['id', 'city', 'street', 'house_number', "apartment_number", 'entrance', 'floor', 'intercom', 'gate', 'comment']);
                },
                'products',
                'paymentMethod' => function ($query) {
                    $query->select(['id', 'name']); 
                },
                'deliveryMethod' => function ($query) {
                    $query->select(['id', 'name']);
            },
            ])->where('user_id', $user['id'])->get();
        }
        $orders->makeHidden(['delivery_method_id', 'payment_method_id', 'address_id']);
        return response()->json(['orders' => $orders], 200);
    }

    public function show($id){
        $user = Auth::guard('sanctum')->user();
        $order = Order::with([
            'user' => function($query){
                $query->select(['id', 'name', 'surname', 'patronymic', 'date_of_birth', 'email', 'phone']);
            },
            'address' => function($query){
                $query->select(['id', 'city', 'street', 'house_number', "apartment_number", 'entrance', 'floor', 'intercom', 'gate', 'comment']);
            },
            'products',
            'paymentMethod' => function ($query) {
                $query->select(['id', 'name']); 
            },
            'deliveryMethod' => function ($query) {
                $query->select(['id', 'name']);
            },
        ])->findOrFail($id);
        $order->makeHidden(['delivery_method_id', 'payment_method_id', 'address_id']);
        if ($order['user_id'] !== $user['id'] && $user->hasRole("Client")){
            return response()->json(['message' => 'Отказано в доступе'], 403);
        }
        else{
            return response()->json(['order' => $order], 200);
        }
    }

    public function store(Request $request){
        $user = Auth::guard('sanctum')->user();
        $totalAmount = 0;
        $validator = Validator::make($request->all(),[
            'delivery_method_id' => 'required|integer',
            'payment_method_id' => 'required|integer',
            'address_id' => 'required|integer',
            'status' => 'required|string',
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (Address::where('user_id', $user->id)->where('id', $request->input('address_id'))->count() === 0) {
            return response()->json(['error' => "Отказано в доступе"], 403);
        }
        
        foreach ($request->products as $productData) {
            $totalAmount += $productData['quantity'] * Product::findOrFail($productData['product_id'])->price;
        }
        $order = Order::create([
            'user_id' => $user['id'],
            'delivery_method_id' => $request->input('delivery_method_id'),
            'payment_method_id' => $request->input('payment_method_id'),
            'address_id' => $request->input('address_id'),
            'status' => $request->input('status'),
            'total_amount' => $totalAmount,
        ]); 
        foreach ($request->products as $productData) {
            $order->products()->attach($productData['product_id'], ['quantity' => $productData['quantity']]);
        }
        return response()->json(['message' => 'Заказ успешно создан'], 201);
    }

    public function update(Request $request, $id){
        $order = Order::with('products')->findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        $totalAmount = 0;
        if ($user->hasRole("Client") && $user->id !== $order->user_id){
            return response()->json(['message' => 'Отказано в доступе'], 403);
        }
        $validator = Validator::make($request->all(),[
            'delivery_method_id' => 'nullable|integer',
            'payment_method_id' => 'nullable|integer',
            'address_id' => 'nullable|integer',
            'status' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('address_id') && Address::where('user_id', $user->id)->where('id', $request->input('address_id'))->count() === 0) {
            return response()->json(['error' => "У вас нет доступа к данному адресу"], 422);
        }

        if ($request->has('products')){
            foreach ($request->products as $productData) {
                if($productData['quantity'] === 0){
                    DB::table('order_product')
                    ->where('order_id', $order->id)
                    ->where('product_id', $productData['product_id'])
                    ->delete();
                    continue;
                }
                $order->products()->syncWithoutDetaching([
                    $productData['product_id'] => ['quantity' => $productData['quantity']]
                ]);
            }
            $orderProducts = DB::table('order_product')
            ->where('order_id', $order->id)
            ->get();
            foreach ($orderProducts as $orderProduct){
                $totalAmount += $orderProduct->quantity * Product::findOrFail($orderProduct->product_id)->price;
            }
            $order->update(['total_amount' => $totalAmount]);    
        }
        $order->update($validator->validate());
        return response()->json(['message' => 'Адрес успешно обновлен'], 200);
    }



    public function destroy($id){
        $order = Order::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Client") && $user['id'] !== $order['user_id']){
            return response()->json(['message' => 'Отказано в доступе'], 403);
        }
        $order->products()->detach();
        $order->delete();
        return response()->json(['message' => 'Заказ был успешно удален'], 200);
    }
}


