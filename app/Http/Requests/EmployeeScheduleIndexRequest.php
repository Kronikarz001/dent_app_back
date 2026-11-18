<?php

namespace App\Http\Requests;

/**
 * Summary of EmployeeScheduleIndexRequest
 */
class EmployeeScheduleIndexRequest extends CalendarEntriesIndexRequest
{
    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'employeeSchedule';
    }
}
