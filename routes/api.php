<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\DeliveryMethodController;


// Регистрация и аутентификация
Route::middleware(['checkrole:Guest'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});


// Просмотр информации о текущем пользователе и выход из аккаунта
Route::middleware(['checkrole:Admin,Client'])->group(function(){
    Route::get('/user', [AuthController::class, 'user']);    
    Route::get('/logout', [AuthController::class, 'logout']);
});


// Просмотр всех пользователей, которые зарегистрированы в приложении
Route::middleware(['checkrole:Admin'])->group(function(){
    Route::get('/users', [AuthController::class, 'users']);
});


// CRUD адреса
Route::middleware(['checkrole:Admin,Client'])->group(function(){
    Route::get('/addresses',[AddressController::class, 'index']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::patch('/addresses/{id}/edit', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
});


// CRUD категории товаров
Route::middleware(['checkrole'])->group(function(){
    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
});
Route::middleware(['checkrole:Admin'])->group(function(){
    Route::post('/product-categories', [ProductCategoryController::class, 'store']);
    Route::patch('/product-categories/{id}/edit', [ProductCategoryController::class, 'update']);
    Route::delete('/product-categories/{id}', [ProductCategoryController::class, 'destroy']);
});


// CRUD товары
Route::middleware(['checkrole'])->group(function(){
    Route::get('/products', [ProductController::class,'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});
Route::middleware(['checkrole:Admin'])->group(function(){
    Route::post('/products', [ProductController::class, 'store']);
    Route::patch('/products/{id}/edit', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});


// CRUD заказы
Route::middleware(['checkrole:Admin,Client'])->group(function(){
    Route::get('/orders', [OrderController::class,'index']);
    Route::get('/orders/{id}', [OrderController::class,'show']);
    Route::post('/orders', [OrderController::class,'store']);
    Route::patch('/orders/{id}/edit', [OrderController::class,'update']);
    Route::delete('/orders/{id}', [OrderController::class,'destroy']);
});


// CRUD способы оплаты
Route::middleware(['checkrole'])->group(function(){
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/payment-methods/{id}', [PaymentMethodController::class, 'show']);
});
Route::middleware(['checkrole:Admin'])->group(function(){
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::patch('/payment-methods/{id}/edit', [PaymentMethodController::class, 'edit']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
});



// CRUD способы доставки
Route::middleware(['checkrole'])->group(function(){
    Route::get('/delivery-methods', [DeliveryMethodController::class, 'index']);
    Route::get('/delivery-methods/{id}', [DeliveryMethodController::class, 'show']);
});
Route::middleware(['checkrole:Admin'])->group(function(){
    Route::post('/delivery-methods', [DeliveryMethodController::class, 'store']);
    Route::patch('/delivery-methods/{id}/edit', [DeliveryMethodController::class, 'edit']);
    Route::delete('/delivery-methods/{id}', [DeliveryMethodController::class, 'destroy']);
});

