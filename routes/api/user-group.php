<?php

use App\Http\Controllers\UserGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/user-group/{userGroup}/users', [UserGroupController::class, 'assignUsers'])
        ->name('user-group.assignUsers');
    Route::patch('/user-group/{userGroup}/permissions', [UserGroupController::class, 'assignPermissions'])
        ->name('user-group.assignPermissions');
    Route::get('/user-group/selectlist', [UserGroupController::class, 'selectList'])
        ->name('user-group.selectList');
    Route::apiResource('/user-group', UserGroupController::class)
        ->names('user-group')
        ->parameters(['user-group' => 'userGroup']);
});
