<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageFileController;
use App\Http\Controllers\MessageGroupController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/message', [MessageController::class, 'index'])
        ->name('message.index');
    Route::post('/message', [MessageController::class, 'store'])
        ->name('message.store');

    Route::apiResource('/message/{message}/file', MessageFileController::class)
        ->names('messagefile');
    Route::get('/message/{message}/file-download/{file}', [MessageFileController::class, 'download'])
        ->name('messagefile.download');

    Route::get('/message-group', [MessageGroupController::class, 'index'])
        ->name('messageGroup.index');
    Route::post('/message-group', [MessageGroupController::class, 'store'])
        ->name('messageGroup.store');
    Route::get('/message-group/{messageGroup}', [MessageGroupController::class, 'show'])
        ->name('messageGroup.show');
    Route::put('/message-group/{messageGroup}', [MessageGroupController::class, 'update'])
        ->name('messageGroup.update');
    Route::delete('/message-group/{messageGroup}', [MessageGroupController::class, 'destroy'])
        ->name('messageGroup.destroy');
    Route::post('/message-group/{messageGroup}/users/{user}', [MessageGroupController::class, 'addUser'])
        ->name('messageGroup.addUser');
    Route::delete('/message-group/{messageGroup}/users/{user}', [MessageGroupController::class, 'removeUser'])
        ->name('messageGroup.removeUser');
    Route::get('/message-group/{messageGroup}/messages', [MessageGroupController::class, 'messages'])
        ->name('messageGroup.messages');
});
