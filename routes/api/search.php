<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search/modules', [SearchController::class, 'modules'])
        ->name('search.modules');
    Route::get('/search', [SearchController::class, 'index'])
        ->name('search.index');
});
