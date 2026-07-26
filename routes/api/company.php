<?php

use App\Http\Controllers\CompanyController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/company', CompanyController::class)
        ->names('company');
});
