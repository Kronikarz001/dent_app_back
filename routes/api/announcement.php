<?php

use App\Http\Controllers\AnnouncementController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/announcement', AnnouncementController::class)
        ->names('announcement');
});
