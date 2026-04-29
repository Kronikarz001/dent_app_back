<?php

use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
    Route::post('/user/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('user.forgot_password');
    Route::patch('/user/{user}/resetPassword', [AuthController::class, 'resetPassword'])
        ->name('user.resetPassword');
});
