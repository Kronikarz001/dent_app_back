<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Search\JobPositionSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of JobPositionRepository
 */
class JobPositionRepository extends SearchableRepository implements JobPositionRepositoryInterface
{
    /**
     * @var string
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

    /**
     * @param string $uuid
     * @return JobPosition|null
     */
    public function findByUuid(string $uuid): ?JobPosition
    {
        return JobPosition::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return JobPosition
     */
    public function create(array $data): JobPosition
    {
        return JobPosition::create($data);
    }

    /**
     * @param JobPosition|Model $model
     * @param array $data
     * @return JobPosition
     */
    public function update(JobPosition|Model $model, array $data): JobPosition
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * @param JobPosition|Model $model
     * @return bool
     */
    public function delete(JobPosition|Model $model): bool
    {
        return $model->delete();
    }
}
