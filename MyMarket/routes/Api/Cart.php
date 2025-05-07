<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Profile\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/addcart/{product}', [CartController::class, 'cart'])->middleware('auth:sanctum');
Route::get('/mycart', [CartController::class, 'getcart'])->middleware('auth:sanctum');
Route::post('/quantity/{id}/{action}', [CartController::class, 'updatequantity'])->middleware('auth:sanctum');
Route::patch('/size/{id}', [CartController::class, 'changeSize'])->middleware('auth:sanctum');
Route::patch('/color/{id}', [CartController::class, 'changeColor'])->middleware('auth:sanctum');
Route::post('/delete/{id}/cart', [CartController::class, 'deletecart'])->middleware('auth:sanctum');
