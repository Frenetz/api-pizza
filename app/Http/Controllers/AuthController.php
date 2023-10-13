<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            "surname" => 'required|string',
            "patronymic" => 'required|string',
            "phone" => 'required|string',
            "date_of_birth" => 'required|date',
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            "surname" => $request->input('surname'),
            "patronymic" => $request->input('patronymic'),
            "phone" => $request->input('phone'),
            "date_of_birth" => $request->input('date_of_birth'),
        ]);
        if (User::count() === 1) {
            $user->assignRole('Admin');
        } else {
            $user->assignRole('Client');
        }
        return response()->json(['message' => 'Пользователь успешно зарегистрирован'], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $credentials = request(['email','password']);
        if (!Auth::attempt($credentials)){
            return response()->json(['message' => 'Неверные учетные данные'], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверные учетные данные'], 422);
        }
        $token = $user->createToken('MyToken')->plainTextToken;
        return response()->json(['token' => $token], 200);
    }

    public function user(){
        return Auth::guard('sanctum')->user();
    }

    public function users(){
        $users = User::with('roles')->get();
        return response()->json(['users' => $users], 200);
    }

    public function logout(Request $request) {
        Auth::guard('sanctum')->user()->tokens()->delete();
        return response()->json(['message' => 'Вы вышли из системы'], 200);
    }
}