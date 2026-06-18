<?php

use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::post('/user/forgot-password', [AuthController::class, 'forgotPassword'])
    ->name('user.forgot_password');
Route::post('/user/reset-password', [AuthController::class, 'resetPassword'])
    ->name('user.reset_password');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
    Route::patch('/user/edit-password', [AuthController::class, 'editPassword'])
        ->name('user.edit_password');
});
