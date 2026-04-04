<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\UserController;


Route::middleware('auth:sanctum')->group(function () {
    /**
     * All api routes for users
     */
    Route::get('/user/{user}/export', [UserController::class, 'export'])
        ->name('user.export');
    Route::post('/user/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('user.forgot_password');
    Route::patch('/user/{user}/resetPassword', [AuthController::class, 'resetPassword'])
        ->name('user.resetPassword');
    Route::get('/user/selectlist', [UserController::class, 'selectList'])
        ->name('user.selectList');
    Route::apiResource('/user', UserController::class)
        ->names('user');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    /**
     * All api routes for job positions
     */
    Route::get('/job-position/{job_position}/export', [JobPositionController::class, 'export'])
        ->name('jobPosition.export');
    Route::patch('/user/{user}/jobposition', [JobPositionController::class, 'assignJobPosition'])
        ->name('user.jobPosition.assignJobPosition');
    Route::get('/job-position/selectlist', [JobPositionController::class, 'selectList'])
        ->name('jobPosition.selectList');
    Route::apiResource('/job-position', JobPositionController::class)
        ->names('jobPosition');
});

Route::post('/login', [AuthController::class, 'login'])
    ->name('auth.login');
