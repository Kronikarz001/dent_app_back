<?php

use App\Http\Controllers\Dictionaries\GeneratorProbeDictionaryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('dictionaries')
    ->name('dictionaries.')
    ->group(function () {
        // Dictionary API routes
        Route::apiResource('generator-probes', GeneratorProbeDictionaryController::class)
            ->names('generator_probes')
            ->parameter('generator-probes', 'dictionary');

    });
