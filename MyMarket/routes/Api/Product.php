<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RateContorller;
use App\Http\Controllers\SearchController;
use Google\Service\CloudIdentity\Group;
use Google\Service\Compute\Router;
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



Route::get('/display', [ProductController::class, 'display']);
Route::get('/display/{id}', [ProductController::class, 'displaybyid']);
Route::get('/similar/{id}/products', [ProductController::class, 'similarproducts']);

Route::post('/Search', [ProductController::class, 'filterbyname']);
Route::get('/Searchcategory/{id}', [ProductController::class, 'filterbycategory']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/admindisplay', [ProductController::class, 'admindisplay']);
    Route::post('/addproduct', [ProductController::class, 'create']);
    Route::post('/notactive/{id}', [ProductController::class, 'notactive']);
    Route::post('/active/{id}', [ProductController::class, 'active']);
    Route::post('/discount', [ProductController::class, 'setdiscount']);
});
Route::get('/discountproducts', [ProductController::class, 'discountproducts']);

Route::post('/product/rate/{id}', [RateContorller::class, 'SendRate'])->middleware('auth:sanctum');

Route::get('/getSizes', [ProductController::class, 'getSizes']);
Route::get('/nameforsearch', [ProductController::class, 'getNameforSearch']);

Route::group(['prefix' => '/search'], function () {
    Route::group(["middleware" => 'auth:sanctum'], function () {
        Route::get("/user", [SearchController::class, "getSearcHistory"]);
        Route::post("/add", [SearchController::class, "setSearchHistory"]);
    });
    Route::get("/popular", [SearchController::class, "getPopularHistory"]);
    Route::get("/getwithCategories", [SearchController::class, "getwithCategories"]);
});
