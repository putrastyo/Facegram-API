<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
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

// GROUP - v1/
Route::prefix('v1')->group(function() {

    // GROUP - auth/
    Route::prefix('auth')->group(function() {

        // AUTHENTICATION
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // PROTECTED GROUP
        Route::middleware('auth:sanctum')->group(function() {

            // AUTHENTICATION - Logout
            Route::post('logout', [AuthController::class, 'logout']);


        });

    });

    // MIDDLEWARE GROUP
    Route::middleware('auth:sanctum')->group(function() {

        // POST
        Route::apiResource('posts', PostController::class);

        // FOLLOW
        Route::post('users/{username}/follow', [FollowController::class, 'follow']);
        Route::delete('users/{username}/unfollow', [FollowController::class, 'unfollow']);

        Route::get('following', [FollowController::class, 'getFollowing']);
        Route::get('users/{username}/followers', [FollowController::class, 'getFollowers']);

        // ACCEPT
        Route::put('users/{username}/accept', [FollowController::class, 'accept']);

        // USER
        Route::get('users', [UserController::class, "index"]);
        Route::get('users/{username}', [UserController::class, "show"]);
    });
});
