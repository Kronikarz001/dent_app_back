<?php

use App\Http\Controllers\PermissionGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/permission-group/{permissionGroup}/permissions', [PermissionGroupController::class, 'assignPermissions'])
        ->name('permission-group.assignPermissions');
    Route::patch('/permission-group/{permissionGroup}/users', [PermissionGroupController::class, 'assignUsers'])
        ->name('permission-group.assignUsers');
    Route::get('/permission-group/selectlist', [PermissionGroupController::class, 'selectList'])
        ->name('permission-group.selectList');
    Route::apiResource('/permission-group', PermissionGroupController::class)
        ->names('permission-group')
        ->parameters(['permission-group' => 'permissionGroup']);
});
