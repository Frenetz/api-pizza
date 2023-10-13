<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index(){
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Admin")){
            $addresses = Address::with('user')->get();
            return response()->json(['addresses' => $addresses], 200);
        }
        elseif ($user->hasRole("Client")){
            $addresses = Address::where('user_id', '=', $user->id)->with('user')->get();
            return response()->json(['addresses' => $addresses], 200);
        }
    }

    public function show($id){
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Admin")){
            $address = Address::with('user')->findOrFail($id);
            return response()->json(['address' => $address], 200);
        }
        elseif ($user->hasRole("Client")){
            $address = Address::with('user')->findOrFail($id);
            if ($user->id === $address->user_id){
                return response()->json(['address' => $address], 200);
            }
            else{
                return response()->json(['message' => 'Отказано в доступе'], 403);
            }
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'city' => 'required|string',
            'street' => 'required|string',
            'house_number' => 'required|integer',
            'apartment_number' => 'nullable|integer',
            'entrance' => 'nullable|string',
            'floor' => 'nullable|integer',
            'intercom' => 'nullable|integer',
            'gate' => 'nullable|boolean',
            'comment' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = Auth::guard('sanctum')->user();
        $address = Address::create([
            'city' => $request->input('city'),
            'street' => $request->input('street'),
            'house_number' => $request->input('house_number'),
            'apartment_number' => $request->input('apartment_number'),
            'entrance' => $request->input('entrance'),
            'floor' => $request->input('floor'),
            'intercom' => $request->input('intercom'),
            'gate' => $request->input('gate'),
            'comment' => $request->input('comment'),
            'user_id' => $user->id
        ]);
        return response()->json(['message' => 'Адрес успешно добавлен'], 201);
    }


    public function update(Request $request, $id){
        $address = Address::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Client") && $user->id !== $address->user_id){
            return response()->json(['message' => 'Отказано в доступе'], 403);
        }
        $validator = Validator::make($request->all(),[
            'city' => 'nullable|string',
            'street' => 'nullable|string',
            'house_number' => 'nullable|integer',
            'apartment_number' => 'nullable|integer',
            'entrance' => 'nullable|string',
            'floor' => 'nullable|integer',
            'intercom' => 'nullable|integer',
            'gate' => 'nullable|boolean',
            'comment' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $address->update($validator->validated());
        return response()->json(['message' => 'Адрес успешно обновлен'], 200);
    }


    public function destroy($id){
        $address = Address::findOrFail($id);
        $user = Auth::guard('sanctum')->user();
        if ($user->hasRole("Client") && $user->id !== $address->user_id){
            return response()->json(['message' => 'Отказано в доступе'], 403);
        }
        $address->delete();
        return response()->json(['message' => 'Адрес успешно удален'], 200);
    }
}
