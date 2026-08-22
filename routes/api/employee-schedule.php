<?php

use App\Http\Controllers\AuditableController;
use App\Http\Controllers\EmployeeScheduleController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employee-schedule/{uuid}/history', [AuditableController::class, 'index'])
        ->defaults('resource', 'employee-schedule')
        ->name('employee-schedule.history');
    Route::get('/employee-schedule/{uuid}/history/export', [AuditableController::class, 'export'])
        ->defaults('resource', 'employee-schedule')
        ->name('employee-schedule.history.export');
    Route::patch('/employee-schedule/{employeeSchedule}/users', [EmployeeScheduleController::class, 'assignUsers'])
        ->name('employee-schedule.assignUsers');
    Route::get('/employee-schedule/selectlist', [EmployeeScheduleController::class, 'selectList'])
        ->name('employee-schedule.selectList');
    Route::apiResource('/employee-schedule', EmployeeScheduleController::class)
        ->names('employee-schedule')
        ->parameters(['employee-schedule' => 'employeeSchedule']);
});
