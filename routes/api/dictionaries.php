<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('dictionaries')
    ->name('dictionaries.')
    ->group(function () {
        // Dictionary API routes
    });
