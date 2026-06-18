<?php

use App\Http\Controllers\AuditableController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/calendar/export', [CalendarController::class, 'export'])
        ->name('calendar.export');
    Route::get('/calendar/{uuid}/history', [AuditableController::class, 'index'])
        ->defaults('resource', 'calendar')
        ->name('calendar.history');
    Route::get('/calendar/{uuid}/history/export', [AuditableController::class, 'export'])
        ->defaults('resource', 'calendar')
        ->name('calendar.history.export');
    Route::patch('/calendar/{calendar}/users', [CalendarController::class, 'assignUsers'])
        ->name('calendar.assignUsers');
    Route::get('/calendar/selectlist', [CalendarController::class, 'selectList'])
        ->name('calendar.selectList');
    Route::apiResource('/calendar', CalendarController::class)
        ->names('calendar');

    Route::apiResource('/calendar/{calendar}/file', CalendarFileController::class)
        ->names('calendarfile');
    Route::get('/calendar/{calendar}/file-download/{file}', [CalendarFileController::class, 'download'])
        ->name('calendarfile.download');
    Route::post('/calendar/{calendar}/file-new-version/{file}', [CalendarFileController::class, 'storeNewVersion'])
        ->name('calendarfile.newversion');
});
