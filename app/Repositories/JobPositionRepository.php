<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Search\JobPositionSearch;
use App\Search\Search;

/**
 * Summary of JobPositionRepository
 *
 * @extends SearchableRepository<JobPosition>
 */
class JobPositionRepository extends SearchableRepository implements JobPositionRepositoryInterface
{
    /**
     * @var class-string<JobPosition>
     */
    protected string $modelClass = JobPosition::class;

    /**
     * @param JobPositionSearch $search
     */
    public function __construct(
        private readonly JobPositionSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
