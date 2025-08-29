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

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/orders', [OrderController::class, 'getorder']);
    Route::get('/admin/orders', [OrderController::class, 'getadminorder']);
    Route::post('/admin/update/status', [OrderController::class, 'orderstatus']);
    Route::get('/admin/orders/{id}', [OrderController::class, 'singleadminorder']);
    Route::post('/addAddress', [OrderController::class, "SaveAddress"]);
    Route::get('/getAddress', [OrderController::class, "getAddress"]);
    Route::delete('/deleteAddress/{id}', [OrderController::class, "deleteAddress"]);
});
Route::get('/availablecity', [OrderController::class, "getAvailableCity"]);
