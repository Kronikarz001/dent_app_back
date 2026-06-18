<?php

use App\Http\Controllers\AuditableController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/{resource}/{uuid}/history', [AuditableController::class, 'index'])
        ->name('auditable.history')
        ->where('resource', 'user|patient|calendar|dental-examination|material|job-position')
        ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}');

    Route::get('/{resource}/{uuid}/history/export', [AuditableController::class, 'export'])
        ->name('auditable.history.export')
        ->where('resource', 'user|patient|calendar|dental-examination|material|job-position')
        ->where('uuid', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}');
});
