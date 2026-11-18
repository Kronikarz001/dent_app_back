<?php

use App\Http\Controllers\StatisticsController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/statistics/users', [StatisticsController::class, 'users'])
        ->name('statistics.users');
    Route::get('/statistics/users/{user}/summary', [StatisticsController::class, 'summary'])
        ->name('statistics.summary');
    Route::get('/statistics/users/{user}/distribution', [StatisticsController::class, 'distribution'])
        ->name('statistics.distribution');
    Route::get('/statistics/users/{user}/daily', [StatisticsController::class, 'daily'])
        ->name('statistics.daily');
});
