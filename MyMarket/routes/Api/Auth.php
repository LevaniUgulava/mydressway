<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\GoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminlogin'])->name('admin.login');
Route::post('auth/facebook', [FacebookController::class, 'authenticate']);
Route::post('google/callback', [GoogleController::class, 'callback']);
Route::post('/email/verify', [AuthController::class, 'verify']);
Route::post('/resend-verify', [AuthController::class, 'resendVerification']);

Route::post('/subscribe', [AuthController::class, 'subscribe']);

Route::group(["middleware" => "auth:api"], function () {
    Route::get('/me', [AuthController::class, 'userInfo']);
    Route::get('/checkoutinfo', [AuthController::class, 'checkoutInfo']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/userstatuses', [AuthController::class, 'getuserstatus']);
    Route::post('/delete/acc', [AuthController::class, 'deleteAcc']);
    Route::post('/send-deactivation-email', [AuthController::class, 'SendDeactivationCode']);
    Route::post('/verify-deactivation-code', [AuthController::class, 'verifyDeactivationCode']);
});
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('throttle:30,1');
