<?php

use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EligibleProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserStatusController;
use App\Models\Userstatus;
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


Route::group(['middleware' => ['auth:api', 'admin'], 'prefix' => '/admin'], function () {
    Route::group(['prefix' => '/userstatus'], function () {
        Route::get('/display', [UserStatusController::class, 'display']);
        Route::post('/create', [UserStatusController::class, 'store']);
        Route::post('/delete/{id}', [UserStatusController::class, 'delete']);
        Route::get('/{id}', [UserStatusController::class, 'StatuswithUser']);
    });
    Route::group(['prefix' => '/eligible'], function () {
        Route::get('/display/{id}', [EligibleProductController::class, 'display']);
        Route::post('/create/{id}', [EligibleProductController::class, 'create']);
        Route::post('/delete/{id}', [EligibleProductController::class, 'delete']);
    });
});
Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/current/status', [UserStatusController::class, 'displayStatus']);
    Route::get('/exlusive', [EligibleProductController::class, 'displayEligibleProduct']);
});
