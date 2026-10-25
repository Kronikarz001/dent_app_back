<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;
use App\Repositories\Concerns\FindsScheduleOverlaps;
use App\Search\EmployeeScheduleSearch;
use App\Search\Search;

/**
 * Summary of EmployeeScheduleRepository
 *
 * @extends SearchableRepository<EmployeeSchedule>
 */
class EmployeeScheduleRepository extends SearchableRepository implements EmployeeScheduleRepositoryInterface
{
    use FindsScheduleOverlaps;

    /**
     * @var class-string<EmployeeSchedule>
     */
    protected string $modelClass = EmployeeSchedule::class;

    /**
     * @param EmployeeScheduleSearch $search
     */
    public function __construct(
        private readonly EmployeeScheduleSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
