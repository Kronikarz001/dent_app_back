<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Search\JobPositionSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of JobPositionRepository
 */
readonly class JobPositionRepository implements JobPositionRepositoryInterface
{
    /**
     * @param  JobPositionSearch  $search
     */
    public function __construct(
        private JobPositionSearch $search
    ) {}

    /**
     * @param  array  $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param  array  $columns
     * @param  array  $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param  string  $uuid
     * @return JobPosition|null
     */
    public function findByUuid(string $uuid): ?JobPosition
    {
        return JobPosition::where('uuid', $uuid)->first();
    }

    /**
     * @param  array  $data
     * @return JobPosition
     */
    public function create(array $data): JobPosition
    {
        return JobPosition::create($data);
    }

    /**
     * @param  JobPosition|Model  $model
     * @param  array  $data
     * @return JobPosition
     */
    public function update(JobPosition|Model $model, array $data): JobPosition
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param  JobPosition|Model  $model
     * @return bool
     */
    public function delete(JobPosition|Model $model): bool
    {
        return $model->delete();
    }
}
