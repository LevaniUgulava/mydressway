<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\GoogleController;
use App\Models\User;
use Google\Service\Compute\Router;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminlogin'])->name('admin.login');
Route::post('auth/facebook', [FacebookController::class, 'authenticate']);
Route::post('google/callback', [GoogleController::class, 'callback']);
Route::post('/email/verify', [AuthController::class, 'verify']);
Route::post('/resend-verify', [AuthController::class, 'ResendVerification']);
Route::group(["middleware" => "auth:sanctum"], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/userstatuses', [AuthController::class, 'getuserstatus']);
    Route::post('/delete/acc', [AuthController::class, 'deleteAcc']);
    Route::post('/send-deactivation-email', [AuthController::class, 'SendDeactivationCode']);
    Route::post('/verify-deactivation-code', [AuthController::class, 'verifyDeactivationCode']);
});
