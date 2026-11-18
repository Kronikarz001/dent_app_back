<?php

namespace App\Services;

use App\Exceptions\ScheduleConflictException;
use App\Models\EmployeeSchedule;
use App\Repositories\EmployeeScheduleRepositoryInterface;
use App\Repositories\ScheduleOverlapRepositoryInterface;
use App\Services\Concerns\DetectsScheduleConflicts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of EmployeeScheduleService
 */
readonly class EmployeeScheduleService implements EmployeeScheduleServiceInterface
{
    use DetectsScheduleConflicts;

    /**
     * @param EmployeeScheduleRepositoryInterface $employeeScheduleRepository
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private EmployeeScheduleRepositoryInterface $employeeScheduleRepository,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return ScheduleOverlapRepositoryInterface
     */
    protected function scheduleRepository(): ScheduleOverlapRepositoryInterface
    {
        return $this->employeeScheduleRepository;
    }

    /**
     * @param array{user_uuids?: string[], date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator
     */
    public function getSchedules(array $filters = []): LengthAwarePaginator
    {
        return $this->employeeScheduleRepository->findAllWithPagination($filters);
    }

    /**
     * @param array{date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator
     */
    public function getMySchedules(array $filters = []): LengthAwarePaginator
    {
        return $this->employeeScheduleRepository->findAllWithPagination(array_merge($filters, [
            'user_uuids' => [Auth::user()->uuid],
        ]));
    }

    /**
     * @return Collection
     */
    public function getSchedulesList(): Collection
    {
        return $this->employeeScheduleRepository->findSelectAll(['uuid', 'name']);
    }

    /**
     * @param EmployeeSchedule $schedule
     * @return EmployeeSchedule
     */
    public function getScheduleDetails(EmployeeSchedule $schedule): EmployeeSchedule
    {
        return $this->employeeScheduleRepository->loadRelations($schedule, ['users:uuid,first_name,last_name']);
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
     *
     * @throws ScheduleConflictException
     */
    public function assignUsers(EmployeeSchedule $schedule, array $data): void
    {
        $this->assertNoScheduleConflicts($schedule, $data['users'] ?? []);

        $this->auditService->recordSync($schedule, 'users', $this->employeeScheduleRepository->syncRelation($schedule, 'users', $data['users'] ?? []));
    }
}
