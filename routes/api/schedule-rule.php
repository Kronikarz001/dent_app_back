<?php

use App\Http\Controllers\ScheduleRuleController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/schedule-rule', ScheduleRuleController::class)
        ->names('schedule-rule')
        ->parameters(['schedule-rule' => 'scheduleRule']);
});
