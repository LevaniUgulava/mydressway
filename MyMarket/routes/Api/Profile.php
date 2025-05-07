<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Profile\CartController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Profiler\Profile;

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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/profile/update', [ProfileController::class, 'Updateprofile']);
    Route::post('/profile/update/password', [ProfileController::class, 'Updatepassword']);
    Route::get('/getprofile', [ProfileController::class, 'getprofile']);
    Route::get('/likeproduct', [ProfileController::class, 'likeproduct']);


    Route::post('/like/{id}', [CartController::class, 'Like']);
    Route::post('/unlike/{id}', [CartController::class, 'unLike']);


    Route::post('/products/addcart/{id}', [CartController::class, 'addcart']);
    Route::get('/products/totalmount', [CartController::class, 'full']);
});
Route::post('/forget/password', [ProfileController::class, 'ResetPassword']);
Route::post('/reset/password', [ProfileController::class, 'ForgotPassword']);
Route::post('/checkotp', [ProfileController::class, 'checkOtp']);
