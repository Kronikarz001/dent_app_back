<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Search\JobPositionSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

class JobPositionRepository extends SearchableRepository implements JobPositionRepositoryInterface
{
    protected string $modelClass = JobPosition::class;

    public function __construct(
        private readonly JobPositionSearch $search
    ) {}

    protected function getSearchModel(): Search
    {
        return $this->search;
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