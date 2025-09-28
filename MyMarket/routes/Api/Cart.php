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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/addcart/{product}', [CartController::class, 'cart']);
    Route::get('/mycart', [CartController::class, 'getcart']);
    Route::post('/quantity/{id}/{action}', [CartController::class, 'updatequantity']);
    Route::post('/quick_update', [CartController::class, 'quickUpdate']);
    Route::patch('/size/{id}', [CartController::class, 'changeSize']);
    Route::patch('/color/{id}', [CartController::class, 'changeColor']);
    Route::post('/delete/{id}/cart', [CartController::class, 'deletecart']);
});
