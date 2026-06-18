<?php

use App\Http\Controllers\AuditableController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\JobPositionFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/job-position/export', [JobPositionController::class, 'export'])
        ->name('jobPosition.export');
    Route::get('/job-position/{uuid}/history', [AuditableController::class, 'index'])
        ->defaults('resource', 'job-position')
        ->name('jobPosition.history');
    Route::get('/job-position/{uuid}/history/export', [AuditableController::class, 'export'])
        ->defaults('resource', 'job-position')
        ->name('jobPosition.history.export');
    Route::patch('/user/{user}/jobposition', [JobPositionController::class, 'assignJobPosition'])
        ->name('user.jobPosition.assignJobPosition');
    Route::get('/job-position/selectlist', [JobPositionController::class, 'selectList'])
        ->name('jobPosition.selectList');
    Route::apiResource('/job-position', JobPositionController::class)
        ->names('jobPosition');

    Route::apiResource('/job-position/{jobPosition}/file', JobPositionFileController::class)
        ->names('jobpositionfile');
    Route::get('/job-position/{jobPosition}/file-download/{file}', [JobPositionFileController::class, 'download'])
        ->name('jobpositionfile.download');
    Route::post('/job-position/{jobPosition}/file-new-version/{file}', [JobPositionFileController::class, 'storeNewVersion'])
        ->name('jobpositionfile.newversion');
});
