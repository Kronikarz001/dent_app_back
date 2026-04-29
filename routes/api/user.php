<?php

use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/user-info', [UserController::class, 'showLoggedUser'])
        ->name('user.user-info');
    Route::get('/user/export', [UserController::class, 'export'])
        ->name('user.export');
    Route::get('/user/selectlist', [UserController::class, 'selectList'])
    ->name('user.selectList');
    Route::apiResource('/user', UserController::class)
        ->names('user');
});
