<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addProduct']);
    Route::post('/cart/remove', [CartController::class, 'removeProduct']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{order}/paid', [OrderController::class, 'markPaid']);
    Route::get('/orders', [OrderController::class, 'list']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
