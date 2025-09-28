<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
Route::group(['prefix' => 'admin/categories', 'middleware' => ['auth:api']], function () {


    Route::group(['middleware' => 'admin'], function () {
        Route::post('/maincategory/create', [CategoryController::class, 'Maincategory']);
        Route::post('/maincategory/delete/{id}', [CategoryController::class, 'Maincategorydelete']);

        Route::post('/category/create', [CategoryController::class, 'category']);
        Route::post('/category/delete/{id}', [CategoryController::class, 'categorydelete']);

        Route::post('/subcategory/create', [CategoryController::class, 'Subcategory']);
        Route::post('/subcategory/delete/{id}', [CategoryController::class, 'Subcategorydelete']);

        Route::get('/admin/category', [CategoryController::class, 'displayadmincategory']);

        Route::post("/assignRelation", [CategoryController::class, "assignMainRelation"]);
        Route::post("/deleteRelation", [CategoryController::class, "deleteMainRelation"]);

        Route::post("/assignCategoryRelation", [CategoryController::class, "assignCategoryRelation"]);
        Route::post("/deleteCategoryRelation", [CategoryController::class, "deleteCategoryRelation"]);
    });
});
Route::get('/maincategory', [CategoryController::class, 'displaymain']);
Route::get('/category', [CategoryController::class, 'displaycategory']);
Route::get('/subcategory', [CategoryController::class, 'displaysub']);
Route::get('/colors', [CategoryController::class, 'displaycolor']);
Route::get('/sizes', [CategoryController::class, 'displaysize']);

Route::get('/maincategory/{id}', [CategoryController::class, 'Maincategorybyid']);
Route::get('/category/{id}', [CategoryController::class, 'categorybyid']);
Route::get('/subcategory/{id}', [CategoryController::class, 'subcategorybyid']);
Route::get('/allCategory', [CategoryController::class, 'allCategory']);
