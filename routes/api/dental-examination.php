<?php

use App\Http\Controllers\AuditableController;
use App\Http\Controllers\DentalExaminationController;
use App\Http\Controllers\DentalExaminationFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dental-examination/export', [DentalExaminationController::class, 'export'])
        ->name('dentalExamination.export');
    Route::get('/dental-examination/{uuid}/history', [AuditableController::class, 'index'])
        ->defaults('resource', 'dental-examination')
        ->name('dentalExamination.history');
    Route::get('/dental-examination/{uuid}/history/export', [AuditableController::class, 'export'])
        ->defaults('resource', 'dental-examination')
        ->name('dentalExamination.history.export');
    Route::get('/dental-examination/selectlist', [DentalExaminationController::class, 'selectList'])
        ->name('dentalExamination.selectList');
    Route::apiResource('/dental-examination', DentalExaminationController::class)
        ->names('dentalExamination');

    Route::apiResource('/dental-examination/{dentalExamination}/file', DentalExaminationFileController::class)
        ->names('dentalexaminationfile');
    Route::get('/dental-examination/{dentalExamination}/file-download/{file}', [DentalExaminationFileController::class, 'download'])
        ->name('dentalexaminationfile.download');
    Route::post('/dental-examination/{dentalExamination}/file-new-version/{file}', [DentalExaminationFileController::class, 'storeNewVersion'])
        ->name('dentalexaminationfile.newversion');
});
