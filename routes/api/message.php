<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/message', [MessageController::class, 'index'])
        ->name('message.index');
    Route::post('/message', [MessageController::class, 'store'])
        ->name('message.store');
    Route::get('/message-group/{messageGroup}/messages', [MessageGroupController::class, 'messages'])
        ->name('messageGroup.messages');
});
