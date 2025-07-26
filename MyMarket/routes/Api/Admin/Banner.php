<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CollectionController;

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

Route::group(['prefix' => '/banner'], function () {
    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => "/admin"], function () {
        Route::post('/create', [BannerController::class, 'create']);
        Route::delete('/delete/{id}', [BannerController::class, 'delete']);
        Route::get('/display', [BannerController::class, 'admindisplay']);
        Route::patch('{id}/active', [BannerController::class, 'active']);
        Route::patch('{id}/notactive', [BannerController::class, 'notactive']);
    });


    Route::get('/display', [BannerController::class, 'display']);
});

Route::group(['prefix' => '/header'], function () {
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/display', [BannerController::class, 'displayHeader']);
        Route::get('/admin/display', [BannerController::class, 'displayHeaderadmin']);
        Route::post('/create', [BannerController::class, 'createHeader']);
        Route::patch('/active/{id}/{action}', [BannerController::class, 'activeHeader']);
    });
});
