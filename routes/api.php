<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::middleware('api')->group(function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/user', [UserController::class, 'getUserProfile']);
});

Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return $request->user();
});
