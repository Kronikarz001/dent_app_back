<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\UserController;


Route::middleware('auth:sanctum')->group(function () {

    /**
     * All api routes for users
     */
    Route::get('/user/selectlist', [UserController::class, 'selectList'])
        ->name('user.selectList');
    Route::apiResource('/user', UserController::class)
        ->names('user');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    /**
     * All api routes for job positions
     */
    Route::apiResource('/job-position', JobPositionController::class)
        ->names('jobPosition');
});

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');
