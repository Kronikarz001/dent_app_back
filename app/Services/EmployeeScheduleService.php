<?php

namespace App\Services;

use App\Models\EmployeeSchedule;
use App\Repositories\EmployeeScheduleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of EmployeeScheduleService
 */
readonly class EmployeeScheduleService implements EmployeeScheduleServiceInterface
{
    /**
     * @param EmployeeScheduleRepositoryInterface $employeeScheduleRepository
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private EmployeeScheduleRepositoryInterface $employeeScheduleRepository,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getSchedules(): LengthAwarePaginator
    {
        return $this->employeeScheduleRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getSchedulesList(): LengthAwarePaginator
    {
        return $this->employeeScheduleRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return EmployeeSchedule
     */
    public function createSchedule(array $data): EmployeeSchedule
    {
        return $this->employeeScheduleRepository->create($data);
    }

    /**
     * @param EmployeeSchedule $schedule
     * @param array $data
     * @return EmployeeSchedule
     */
    public function updateSchedule(EmployeeSchedule $schedule, array $data): EmployeeSchedule
    {
        return $this->employeeScheduleRepository->update($schedule, $data);
    }

    /**
     * @param EmployeeSchedule $schedule
     * @return void
     */
    public function deleteSchedule(EmployeeSchedule $schedule): void
    {
        $this->employeeScheduleRepository->delete($schedule);
    }

    /**
     * @param EmployeeSchedule $schedule
     * @param array $data
     * @return void
     */
    public function assignUsers(EmployeeSchedule $schedule, array $data): void
    {
        $this->auditService->recordSync($schedule, 'users', $schedule->users()->sync($data['users'] ?? []));
    }
}
