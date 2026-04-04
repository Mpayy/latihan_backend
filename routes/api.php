<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
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

Route::get('posts', [PostController::class,'index']);
Route::get('posts/{post}', [PostController::class,'show']);

Route::middleware('auth:sanctum')->group(function(){
    Route::apiResource('users', UserController::class);
    Route::apiResource('posts', PostController::class)->except('index', 'show');
    Route::post('posts/{post}/likes', [LikeController::class, 'toggleLike']);
});