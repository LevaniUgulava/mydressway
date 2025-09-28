<?php

use App\Http\Controllers\SendGridController;
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

Route::group(['middleware' => ['auth:api']], function () {});


Route::get('/templates', [SendGridController::class, "getAllTemplate"]);
Route::get('/template/{id}', [SendGridController::class, "getOneTemplate"]);
Route::post('/template/{templateId}/version/{versionId}', [SendGridController::class, "Activate"]);
Route::post('/addtemplate', [SendGridController::class, "AddTemplate"]);
Route::post('/sendgrid/addContact', [SendGridController::class, "addContact"]);
