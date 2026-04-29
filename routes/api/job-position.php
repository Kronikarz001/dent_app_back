<?php

use App\Http\Controllers\JobPositionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/job-position/export', [JobPositionController::class, 'export'])
        ->name('jobPosition.export');
    Route::patch('/user/{user}/jobposition', [JobPositionController::class, 'assignJobPosition'])
        ->name('user.jobPosition.assignJobPosition');
    Route::get('/job-position/selectlist', [JobPositionController::class, 'selectList'])
        ->name('jobPosition.selectList');
    Route::apiResource('/job-position', JobPositionController::class)
        ->names('jobPosition');
});
