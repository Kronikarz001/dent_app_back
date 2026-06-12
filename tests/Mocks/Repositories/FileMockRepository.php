<?php

namespace Tests\Mocks\Repositories;

use App\Models\File;
use App\Repositories\FileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;

class FileMockRepository implements FileRepositoryInterface
{
    public function create(array $data): File
    {
        return File::factory()->make($data);
    }

    public function findByUuid(string $uuid): File
    {
        return new File;
    }

    public function update(Model|File $model, array $data): File
    {
        return File::factory()->make($data);
    }

    public function delete(Model|File $model): bool
    {
        return true;
    }

    public function findAllByUuids(array $uuids): Collection
    {
        return new Collection;
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

    public function findAllByFileUuid(string $uuid): Collection
    {
        return new Collection;
    }

    public function findAllByFileableExceptUuid(string $fileableType, string $fileableId, string $excludeUuid, ?string $oldFileUuid): Collection
    {
        return new Collection;
    }

    public function getRootUuid(string $fileableType, string $fileableId): ?string
    {
        return null;
    }

    public function findAllWithParams(array $params): Collection
    {
        return new Collection;
    }
}
