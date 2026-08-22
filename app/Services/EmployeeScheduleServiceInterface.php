<?php

namespace App\Services;

use App\Models\EmployeeSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of EmployeeScheduleServiceInterface
 */
interface EmployeeScheduleServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getSchedules(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getSchedulesList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return EmployeeSchedule
     */
    public function createSchedule(array $data): EmployeeSchedule;

    /**
     * @param EmployeeSchedule $schedule
     * @param array $data
     * @return EmployeeSchedule
     */
    public function updateSchedule(EmployeeSchedule $schedule, array $data): EmployeeSchedule;

    /**
     * @param EmployeeSchedule $schedule
     * @return void
     */
    public function deleteSchedule(EmployeeSchedule $schedule): void;

    /**
     * @param EmployeeSchedule $schedule
     * @param array $data
     * @return void
     */
    public function assignUsers(EmployeeSchedule $schedule, array $data): void;
}
