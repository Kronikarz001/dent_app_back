<?php

namespace Tests\Mocks\Repositories;

use App\Models\JobPosition;
use App\Repositories\JobPositionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;

class JobPositionMockRepository implements JobPositionRepositoryInterface
{
    public function findByUuid(string $uuid): ?JobPosition
    {
        return new JobPosition();
    }

    public function create(array $data): JobPosition
    {
        return JobPosition::factory()->make($data);
    }

    public function update(JobPosition|Model $model, array $data): JobPosition
    {
        return JobPosition::factory()->make($data);
    }

    public function delete(Model|JobPosition $model): bool
    {
        return true;
    }

    public function findAllByUuids(array $uuids): Collection
    {
        return new Collection();
    }

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): Model
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }
}
