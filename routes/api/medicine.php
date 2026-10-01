<?php

use App\Http\Controllers\MedicineController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/medicine/selectlist', [MedicineController::class, 'selectList'])
        ->name('medicine.selectList');
    Route::apiResource('/medicine', MedicineController::class)
        ->names('medicine');
});
