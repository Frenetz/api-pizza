<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with('category')->get();
        return response()->json(['products' => $products], 200);
    }

    public function show($id){
        $product = Product::with('category')->findOrFail($id);
        return response()->json(['product' => $product], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string',
            'category_id' => 'required|integer',
            'composition' => 'required|string',
            'calories' => 'required|integer',
            'price' => 'required|integer'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $product = Product::create([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'composition' => $request->input('composition'),
            'calories' => $request->input('calories'),
            'price' => $request->input('price')
        ]);
        return response()->json(['message' => 'Продукт был успешно создан'], 201);
    }

    public function update(Request $request, $id){
        $product = Product::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'name' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'composition' => 'nullable|string',
            'calories' => 'nullable|integer',
            'price' => 'nullable|integer'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $product->update($validator->validate());
        return response()->json(['message' => 'Продукт был успешно обновлен'],200);
    }

    public function destroy($id){
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message'=> 'Продукт был успешно удален'], 200);
    }
}
