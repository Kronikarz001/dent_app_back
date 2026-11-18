<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;
use App\Repositories\Concerns\FindsScheduleOverlaps;
use App\Search\EmployeeScheduleSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    /**
     * @param string[] $userUuids
     * @param string $dateFrom
     * @param string $dateTo
     * @return Collection<int, EmployeeSchedule>
     */
    public function findActiveForUsersBetween(array $userUuids, string $dateFrom, string $dateTo): Collection
    {
        $forUsers = fn (Builder|MorphToMany $users) => $users->whereIn('users.uuid', $userUuids);

        return $this->query()
            ->where('is_active', true)
            ->whereDate('date', '<=', $dateTo)
            ->whereRaw('COALESCE(end_date, date) >= ?', [$dateFrom])
            ->whereHas('users', $forUsers)
            ->with(['users' => fn (MorphToMany $users) => $forUsers($users)->select('users.uuid')])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }
}
