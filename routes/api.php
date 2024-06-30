<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
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


Route::prefix('/')->middleware('auth:sanctum')->group(function () {
    Route::delete('logout', [AuthController::class, 'destroy']);

    Route::prefix('posts')->group(function () {
        Route::get('new', [PostController::class, 'newPost']);
        Route::get('me', [PostController::class, 'index']);
        Route::get('followed', [PostController::class, 'followedPost']);
        
        Route::post('{post}/images', [PostController::class, 'addImages']);
    });
    Route::apiResource('posts', PostController::class)->except(['index']);

    Route::prefix('categories')->group(function () {
        Route::get('', [CategoryController::class, 'fetchAll']);
    });
});


Route::prefix('/')->group(function () {
    Route::post('register', [AuthController::class, 'store']);
    Route::post('login', [AuthController::class, 'authenticate']);
});
