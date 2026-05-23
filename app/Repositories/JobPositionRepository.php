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
    public function __construct(
        private JobPositionSearch $search
    ) {}

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    public function findByUuid(string $uuid): ?JobPosition
    {
        return JobPosition::where('uuid', $uuid)->first();
    }

    public function create(array $data): JobPosition
    {
        return JobPosition::create($data);
    }

    public function update(JobPosition|Model $model, array $data): JobPosition
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(JobPosition|Model $model): bool
    {
        return $model->delete();
    }
}
