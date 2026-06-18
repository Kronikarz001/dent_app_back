<?php

use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/material/export', [MaterialController::class, 'export'])
        ->name('material.export');
    Route::get('/material/selectlist', [MaterialController::class, 'selectList'])
        ->name('material.selectList');
    Route::apiResource('/material', MaterialController::class)
        ->names('material');

    Route::apiResource('/material/{material}/file', MaterialFileController::class)
        ->names('materialfile');
    Route::get('/material/{material}/file-download/{file}', [MaterialFileController::class, 'download'])
        ->name('materialfile.download');
    Route::post('/material/{material}/file-new-version/{file}', [MaterialFileController::class, 'storeNewVersion'])
        ->name('materialfile.newversion');
});
