<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReplyController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
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

    Route::prefix('users')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::post('me', [UserController::class, 'edit']);
        
        Route::prefix('{user}')->group(function () {
            Route::get('', [UserController::class, 'show']);
            Route::get('posts', [UserController::class, 'getPosts']);
        });

    });

    Route::prefix('posts')->group(function () {
        Route::get('new', [PostController::class, 'newPost']);
        Route::get('me', [PostController::class, 'index']);
        Route::get('followed', [PostController::class, 'followedPost']);
        
        Route::prefix('{post}')->group(function () {
            Route::post('images', [PostController::class, 'addFiles']);
            
            Route::get('replies', [PostReplyController::class, 'index']);
            Route::post('replies', [PostReplyController::class, 'store']);
        });

        Route::prefix('replies/{postReply}')->group(function () {
            Route::patch('', [PostReplyController::class, 'update']);
            Route::delete('', [PostReplyController::class, 'destroy']);

            Route::post('up', [PostReplyController::class, 'upVote']);
            
            Route::post('images', [PostReplyController::class, 'addFiles']);
        });
    });
    Route::apiResource('posts', PostController::class)->except(['index']);

    Route::prefix('survey')->group(function () {
        Route::post('options/{surveyOption}', [SurveyController::class, 'reply']);
        Route::delete('options/{surveyOption}', [SurveyController::class, 'deleteReply']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('', [CategoryController::class, 'fetchAll']);
    });
});


Route::prefix('/')->group(function () {
    Route::post('register', [AuthController::class, 'store']);
    Route::post('login', [AuthController::class, 'authenticate']);
});
