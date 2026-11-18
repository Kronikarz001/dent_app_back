<?php

use App\Http\Controllers\RoomController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/room/selectlist', [RoomController::class, 'selectList'])
        ->name('room.selectList');
    Route::apiResource('/room', RoomController::class)
        ->names('room');
});
