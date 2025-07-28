<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReplyController;
use App\Http\Controllers\ReportingCategoryController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRelationController;
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


            Route::prefix('relations')->group(function () {
                Route::post('request', [UserRelationController::class, 'sendRequest']);
                Route::post('blocked', [UserRelationController::class, 'blocked']);
                Route::post('unblocked', [UserRelationController::class, 'unblocked']);
                Route::post('request/reply', [UserRelationController::class, 'replyRequest']);

                Route::delete('contact', [UserRelationController::class, 'removeContact']);
            });
        });
        
        Route::prefix('relations')->group(function () {
            Route::prefix('{userRelationType:name}')->where(['name' => 'contact|request|blocked'])->group(function () {
                Route::get('', [UserRelationController::class, 'getRelations']);
                Route::get('select', [UserRelationController::class, 'getRelationsForSelect']);
            });
        });
    });

    Route::prefix('posts')->group(function () {
        Route::get('me', [PostController::class, 'index']);
        Route::get('new', [PostController::class, 'newPost']);
        Route::get('followed', [PostController::class, 'followedPost']);
        Route::get('favorite', [PostController::class, 'favoritePost']);
        
        Route::prefix('{post}')->group(function () {
            Route::post('files', [PostController::class, 'addFiles']);
            
            Route::get('replies', [PostReplyController::class, 'index']);
            Route::post('replies', [PostReplyController::class, 'store']);

            Route::post('favorite', [PostController::class, 'favorite']);
            Route::post('share', [PostController::class, 'share']);
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
        Route::get('', [CategoryController::class, 'fetchForSelect']);
    });
    Route::prefix('reporting-categories')->group(function () {
        Route::get('{type}', [ReportingCategoryController::class, 'fetchForSelect'])->where('type', 'bug|post');
    });

    Route::prefix('conversations')->group(function () {
        Route::get('', [ConversationController::class, 'getConversations']);
        Route::post('', [ConversationController::class, 'getOrCreate']);
        Route::post('{conversation}/close', [ConversationController::class, 'hide']);
        
        Route::prefix('{conversation}')->group(function () {
            Route::prefix('messages')->group(function () {
                Route::get('', [MessageController::class, 'get']);
                Route::post('', [MessageController::class, 'store']);
            });
        });
    });

    Route::prefix('messages')->group(function () {
        Route::put('{message}', [MessageController::class, 'update']);
        Route::delete('{message}', [MessageController::class, 'destroy']);
    });

    Route::prefix('search')->group(function () {
        Route::get('users', [UserController::class, 'fetchUsers']);
    });

    Route::prefix('bug-reports')->group(function () {
        Route::post('', [ReportingController::class, 'bugReport']);
    });

    // Admin Route
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::prefix('categories')->group(function () {
            Route::get('', [CategoryController::class, 'fetchAll']);
            Route::post('', [CategoryController::class, 'store']);
            
            Route::put('{category}', [CategoryController::class, 'update']);
            Route::delete('{category}', [CategoryController::class, 'destroy']);
            Route::post('{id}/restore', [CategoryController::class, 'restore']);
        });
        
        Route::prefix('reporting-categories')->group(function () {
            Route::get('', [ReportingCategoryController::class, 'fetchAll']);
            Route::post('', [ReportingCategoryController::class, 'store']);
            
            Route::put('{category}', [ReportingCategoryController::class, 'update']);
            Route::delete('{category}', [ReportingCategoryController::class, 'destroy']);
            Route::post('{id}/restore', [ReportingCategoryController::class, 'restore']);
        });

        Route::prefix('bug-reports')->group(function () {
            Route::get('stats', [ReportingController::class, 'getStats']);
            
            Route::get('{status}', [ReportingController::class, 'fetchAll'])->where(['status' => 'opened|processed|done']);
            Route::prefix('{bugReport}')->group(function () {
                Route::patch('status', [ReportingController::class, 'updateStatus']);
                Route::patch('', [ReportingController::class, 'update']);
            });
        });
    });
});


Route::prefix('/')->group(function () {
    Route::post('register', [AuthController::class, 'store']);
    Route::post('login', [AuthController::class, 'authenticate']);
});
