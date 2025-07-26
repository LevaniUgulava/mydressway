<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
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

Route::post('/checkout', [PaymentController::class, 'checkout'])->middleware('auth:sanctum');


Route::get('/manage/{id}/{size}/{color}/{quantity}', [PaymentController::class, 'managequantity']);
Route::delete('delete/temporder', [PaymentController::class, 'deleteTempOrder']);

Route::group(["middleware" => ["auth:sanctum", "verified"]], function () {
    Route::get('/updatestatus', [PaymentController::class, 'updatestatus']);
});
Route::get('/get/temporder', [PaymentController::class, 'getTemporder'])->middleware(["userverified", 'auth:sanctum']);
Route::post('/temporder', [PaymentController::class, 'Temporder'])->middleware("userverified");
