<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;







Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function(){
    return response()->json(['message'=>'API has Run !!', 'version' => '1.0']);
});

// Memasang "Pintu" (Routing) register
Route::post('register', [AuthController::class, 'register']);

// Memasang "Pintu" (Routing) login
Route::post('login', [AuthController::class, 'login']);

// Route::get('posts', [PostController::class,'index']);
// Route::get('posts/{post}', [PostController::class,'show']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('posts', PostController::class);
    Route::post('posts/{post}/likes', [LikeController::class, 'toggleLike']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::get('posts/{post}/comments', [CommentController::class, 'indexByPost']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('users/{user}/follows', [FollowController::class, 'toggleFollow']);
    Route::get('users/{user}/followers', [FollowController::class, 'getFollowers']);
    Route::get('users/{user}/following', [FollowController::class, 'getFollowing']);
    Route::get('profile', [ProfileController::class, 'getProfile']);
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::get('feeds', [FeedController::class, 'index']);
});