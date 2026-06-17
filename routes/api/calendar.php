<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/calendar/export', [CalendarController::class, 'export'])
        ->name('calendar.export');
    Route::patch('/user/{user}/calendar', [CalendarController::class, 'assignCalendar'])
        ->name('user.calendar.assignCalendar');
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
