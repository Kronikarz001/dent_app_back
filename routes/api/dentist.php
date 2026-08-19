<?php

use App\Http\Controllers\DentistController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dentist/selectlist', [DentistController::class, 'selectList'])
        ->name('dentist.selectList');
    Route::apiResource('/dentist', DentistController::class)
        ->only(['index', 'show'])
        ->names('dentist');
});
