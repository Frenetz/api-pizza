<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryMethod;
use Illuminate\Support\Facades\Validator;


class DeliveryMethodController extends Controller
{
    public function index(){
        $deliveryMethods = DeliveryMethod::all();
        return response()->json(['delivery-methods' => $deliveryMethods], 200);
    }

    public function show($id){
        $deliveryMethod = DeliveryMethod::findOrFail($id);
        return response()->json(['delivery-method' => $deliveryMethod], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), ['name' => 'required|string']);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        DeliveryMethod::create(['name' => $request->input('name')]);
        return response()->json(['message' => "Способ доставки был успешно создан"], 201);
    }

    public function edit(Request $request, $id){
        $validator = Validator::make($request->all(), ['name' => 'required|string']);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $deliveryMethod = DeliveryMethod::findOrFail($id);
        $deliveryMethod->update($validator->validated());
        return response()->json(['message' => 'Способ доставки был успешно обновлен'], 200);
    }

    public function destroy($id){
        $deliveryMethod = DeliveryMethod::findOrFail($id);
        $deliveryMethod->delete();
        return response()->json(['message' => "Способ доставки был успешно удален"], 200);
    }
}
