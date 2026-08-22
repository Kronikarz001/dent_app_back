<?php

use App\Http\Controllers\DepartmentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/department/{department}/roles', [DepartmentController::class, 'assignRoles'])
        ->name('department.assignRoles');
    Route::patch('/department/{department}/job-positions', [DepartmentController::class, 'assignJobPositions'])
        ->name('department.assignJobPositions');
    Route::post('/department/{department}/managed-roles', [DepartmentController::class, 'createRole'])
        ->name('department.createRole');
    Route::patch('/department/{department}/users', [DepartmentController::class, 'assignUsers'])
        ->name('department.assignUsers');
    Route::patch('/department/{department}/permissions', [DepartmentController::class, 'assignPermissions'])
        ->name('department.assignPermissions');
    Route::post('/department/{department}/delegate', [DepartmentController::class, 'delegate'])
        ->name('department.delegate');
    Route::get('/department/selectlist', [DepartmentController::class, 'selectList'])
        ->name('department.selectList');
    Route::apiResource('/department', DepartmentController::class)
        ->names('department');
});
