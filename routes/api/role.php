<?php

use App\Http\Controllers\RoleController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/role/{role}/users', [RoleController::class, 'assignUsers'])
        ->name('role.assignUsers');
    Route::patch('/role/{role}/permissions', [RoleController::class, 'assignPermissions'])
        ->name('role.assignPermissions');
    Route::post('/role/{role}/delegate', [RoleController::class, 'delegate'])
        ->name('role.delegate');
    Route::get('/role/selectlist', [RoleController::class, 'selectList'])
        ->name('role.selectList');
    Route::apiResource('/role', RoleController::class)
        ->names('role');
});
