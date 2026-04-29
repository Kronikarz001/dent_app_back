<?php

use App\Http\Controllers\PatientController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patient/export', [PatientController::class, 'export'])
        ->name('patient.export');
    Route::get('/patient/selectlist', [PatientController::class, 'selectList'])
        ->name('patient.selectList');
    Route::apiResource('/patient', PatientController::class)
        ->names('patient');
});
