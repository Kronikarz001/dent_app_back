<?php

use App\Http\Controllers\AuditableController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientFileController;
use App\Http\Controllers\PatientPhotoController;
use App\Http\Controllers\PrescriptionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patient/export', [PatientController::class, 'export'])
        ->name('patient.export');
    Route::get('/patient/{uuid}/history', [AuditableController::class, 'index'])
        ->defaults('resource', 'patient')
        ->name('patient.history');
    Route::get('/patient/{uuid}/history/export', [AuditableController::class, 'export'])
        ->defaults('resource', 'patient')
        ->name('patient.history.export');
    Route::get('/patient/selectlist', [PatientController::class, 'selectList'])
        ->name('patient.selectList');
    Route::get('/patient/romexis-sync-list', [PatientController::class, 'romexisSyncList'])
        ->name('patient.romexisSyncList');
    Route::apiResource('/patient', PatientController::class)
        ->names('patient');

    Route::apiResource('/patient/{patient}/file', PatientFileController::class)
        ->names('patientfile');
    Route::get('/patient/{patient}/file-download/{file}', [PatientFileController::class, 'download'])
        ->name('patientfile.download');
    Route::post('/patient/{patient}/file-new-version/{file}', [PatientFileController::class, 'storeNewVersion'])
        ->name('patientfile.newversion');

    Route::apiResource('/patient/{patient}/photo', PatientPhotoController::class)
        ->names('patientphoto');
    Route::get('/patient/{patient}/photo-download/{photo}', [PatientPhotoController::class, 'download'])
        ->name('patientphoto.download');

    Route::get('/patient/{patient}/prescription', [PrescriptionController::class, 'byPatient'])
        ->name('patient.prescriptions');

    Route::get('/patient/{patient}/statistics', [PatientController::class, 'statistics'])
        ->name('patient.statistics');
    Route::get('/patient/{patient}/visits', [PatientController::class, 'visits'])
        ->name('patient.visits');
});
