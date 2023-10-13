<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    public function index(){
        $categories = ProductCategory::all();
        return response()->json(['product-categories' => $categories], 200);
    }

    public function show($id){
        $category = ProductCategory::findOrFail($id);
        return response()->json(['product-category' => $category], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|min:3'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $category = ProductCategory::create([
            'name' => $request->input('name')
        ]);
        return response()->json(['message' => 'Категория товаров была успешно добавлена'], 201);
    }

    public function update(Request $request, $id){
        $category = ProductCategory::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'name' => 'nullable|string|min:3'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $category->update($validator->validate());
        return response()->json(['message' => 'Категория товаров была успешно обновлена'], 200);
    }

    public function destroy($id){
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Категория товаров была успешно удалена'], 200);
    }
}
