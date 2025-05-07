<?php

use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
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

 Route::group(['prefix' => 'admin/roles', 'middleware' => ['adminpanel', 'admin', 'auth:sanctum']], function () {
     Route::get('/', [RolesController::class, 'display']);
     Route::post('/change/user/{user}/role/{role}', [RolesController::class, 'update']);
     Route::post('/remove/user/{user}/role/{role}', [RolesController::class, 'remove']);
 });
