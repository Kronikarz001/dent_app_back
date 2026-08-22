<?php

use App\Http\Controllers\PermissionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/permission/selectlist', [PermissionController::class, 'selectList'])
        ->name('permission.selectList');
    Route::apiResource('/permission', PermissionController::class)
        ->only(['index', 'show'])
        ->names('permission');
});
