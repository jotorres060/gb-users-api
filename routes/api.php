<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/users', [\App\Http\Controllers\Api\UserController::class, "index"]);

Route::get('/users/{id}', [\App\Http\Controllers\Api\UserController::class, "show"])->where('id', '[0-9]+');

Route::post('/users/create', [\App\Http\Controllers\Api\UserController::class, "store"]);

Route::put('/users/edit/{id}', [\App\Http\Controllers\Api\UserController::class, "update"])->where('id', '[0-9]+');

Route::delete('/users/delete/{id}', [\App\Http\Controllers\Api\UserController::class, "destroy"])->where('id', '[0-9]+');
