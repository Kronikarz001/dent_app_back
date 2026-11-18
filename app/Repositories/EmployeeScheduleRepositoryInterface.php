<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;
use Illuminate\Database\Eloquent\Collection;

/**
 * Summary of EmployeeScheduleRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<EmployeeSchedule>
 */
interface EmployeeScheduleRepositoryInterface extends ScheduleOverlapRepositoryInterface, SearchableRepositoryInterface
{
    /**
     * @param string[] $userUuids
     * @param string $dateFrom
     * @param string $dateTo
     * @return Collection<int, EmployeeSchedule>
     */
    public function findActiveForUsersBetween(array $userUuids, string $dateFrom, string $dateTo): Collection;
}
