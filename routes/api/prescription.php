<?php

use App\Http\Controllers\PrescriptionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/prescription', PrescriptionController::class)
        ->names('prescription');
    Route::post('/prescription/{prescription}/issue', [PrescriptionController::class, 'issue'])
        ->name('prescription.issue');
});
