<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    public function index(){
        $paymentMethods = PaymentMethod::all();
        return response()->json(['payment-methods' => $paymentMethods], 200);
    }

    public function show($id){
        $paymentMethod = PaymentMethod::findOrFail($id);
        return response()->json(['payment-method' => $paymentMethod], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), ['name' => 'required|string']);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        PaymentMethod::create(['name' => $request->input('name')]);
        return response()->json(['message' => 'Способ оплаты был успешно добавлен'], 201);
    }

    public function edit(Request $request, $id){
        $validator = Validator::make($request->all(), ['name' => 'required|string']);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->update($validator->validated());
        return response()->json(['message' => 'Способ оплаты был успешно обновлен'], 200);
    }

    public function destroy($id){
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();
        return response()->json(['message' => 'Способ оплаты был успешно удален'], 200);
    }
}
