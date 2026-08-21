<?php

use App\Http\Controllers\RoleGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/role-group/{roleGroup}/roles', [RoleGroupController::class, 'assignRoles'])
        ->name('role-group.assignRoles');
    Route::patch('/role-group/{roleGroup}/users', [RoleGroupController::class, 'assignUsers'])
        ->name('role-group.assignUsers');
    Route::patch('/role-group/{roleGroup}/permissions', [RoleGroupController::class, 'assignPermissions'])
        ->name('role-group.assignPermissions');
    Route::post('/role-group/{roleGroup}/delegate', [RoleGroupController::class, 'delegate'])
        ->name('role-group.delegate');
    Route::get('/role-group/selectlist', [RoleGroupController::class, 'selectList'])
        ->name('role-group.selectList');
    Route::apiResource('/role-group', RoleGroupController::class)
        ->names('role-group')
        ->parameters(['role-group' => 'roleGroup']);
});
