<?php

use App\Http\Controllers\Admin\BrandController;
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


Route::group(['prefix' => 'admin/collection', 'middleware' => ['adminpanel', 'auth:sanctum']], function () {

    Route::post('/create', [CollectionController::class, 'create']);
    Route::post('/delete/{collection}', [CollectionController::class, 'deletecollection']);
    Route::get('/{collection}', [CollectionController::class, 'singleadmincollection']);
    Route::post('/addtocollection/{collection}/product/{product}', [CollectionController::class, 'addtocollection']);
    Route::get('/display/products/{collection}', [CollectionController::class, 'allProductsForCollection']);
});
Route::group(['prefix' => 'admin/brand', 'middleware' => ['adminpanel', 'auth:sanctum']], function () {
    Route::get('/display', [BrandController::class, 'admindisplay']);
    Route::get('/display/{id}', [BrandController::class, 'displaybyid']);
    Route::post('/create', [BrandController::class, 'create']);
    Route::put('/update/{id}', [BrandController::class, 'update']);
    Route::delete('/delete/{id}', [BrandController::class, 'delete']);
    Route::post('/notactive/{id}', [BrandController::class, 'notactive']);
    Route::post('/active/{id}', [BrandController::class, 'active']);
});



Route::get('/collection/display', [CollectionController::class, 'getcollection']);
Route::get('/product/collection/{collection}', [CollectionController::class, 'singlecollection']);
Route::get('/brand/display', [BrandController::class, 'display']);
