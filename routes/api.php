<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/selectlist', [UserController::class, 'selectList'])
        ->name('user.selectList');
    Route::apiResource('/user', UserController::class)
        ->names('user');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
});

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');
