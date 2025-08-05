<?php

use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromocodeController;
use App\Services\PromocodeService;
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


Route::group(['prefix' => 'admin/promocode', 'middleware' => ['admin', 'auth:sanctum']], function () {
    Route::get('/', [PromocodeController::class, 'display']);
    Route::get('/{id}', [PromocodeController::class, 'displayByid']);
});
Route::get('/', [PromocodeController::class, 'display']);
Route::get('/{id}', [PromocodeController::class, 'displayByid']);
Route::post('/create', [PromocodeController::class, 'create']);
Route::post('/createrelation', [PromocodeController::class, 'createRelation']);


Route::post('/test/promo', [PromocodeService::class, 'applytoPromocode'])->middleware('auth:sanctum');
