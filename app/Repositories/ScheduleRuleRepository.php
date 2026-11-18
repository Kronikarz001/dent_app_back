<?php

namespace App\Repositories;

use App\Models\ScheduleRule;
use App\Search\ScheduleRuleSearch;
use App\Search\Search;

/**
 * Summary of ScheduleRuleRepository
 *
 * @extends SearchableRepository<ScheduleRule>
 */
class ScheduleRuleRepository extends SearchableRepository implements ScheduleRuleRepositoryInterface
{
    /**
     * @var class-string<ScheduleRule>
     */
    protected string $modelClass = ScheduleRule::class;

    /**
     * @param ScheduleRuleSearch $search
     */
    public function __construct(
        private readonly ScheduleRuleSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
