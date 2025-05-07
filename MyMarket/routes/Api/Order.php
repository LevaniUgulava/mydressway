<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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

Route::get('/orders', [OrderController::class, 'getorder'])->middleware('auth:sanctum');

Route::group(['middleware' => 'adminpanel'], function () {
    Route::get('/admin/orders', [OrderController::class, 'getadminorder'])->middleware('auth:sanctum');
    Route::post('/admin/update/status', [OrderController::class, 'orderstatus'])->middleware('auth:sanctum');
    Route::get('/admin/orders/{id}', [OrderController::class, 'singleadminorder'])->middleware('auth:sanctum');
});
