<?php

namespace App\Services;

use App\Models\EmployeeSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of EmployeeScheduleServiceInterface
 */
interface EmployeeScheduleServiceInterface
{
    /**
     * @param array{user_uuids?: string[], date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator
     */
    public function getSchedules(array $filters = []): LengthAwarePaginator;

    /**
     * @param array{date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator
     */
    public function getMySchedules(array $filters = []): LengthAwarePaginator;

    /**
     * @return Collection
     */
    public function getSchedulesList(): Collection;

    /**
     * @param EmployeeSchedule $schedule
     * @return EmployeeSchedule
     */
    public function getScheduleDetails(EmployeeSchedule $schedule): EmployeeSchedule;

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
