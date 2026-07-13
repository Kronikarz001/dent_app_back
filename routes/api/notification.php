<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-as-read');
    Route::get('/notifications/preferences', [NotificationController::class, 'getPreferences'])
        ->name('notifications.preferences.index');
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])
        ->name('notifications.preferences.update');
});
