<?php

namespace Tests\Mocks\Repositories;

use App\Models\File;
use App\Repositories\FileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;

/**
 * Summary of FileMockRepository
 */
class FileMockRepository implements FileRepositoryInterface
{
    /**
     * @param array $data
     * @return File
     */
    public function create(array $data): File
    {
        return File::factory()->make($data);
    }

    /**
     * @param string $uuid
     * @return File
     */
    public function findByUuid(string $uuid): File
    {
        return new File;
    }

    /**
     * @param Model|File $model
     * @param array $data
     * @return File
     */
    public function update(Model|File $model, array $data): File
    {
        return File::factory()->make($data);
    }

    /**
     * @param Model|File $model
     * @return bool
     */
    public function delete(Model|File $model): bool
    {
        return true;
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return new Collection;
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param string $modelClass
     * @param array $uniqueAttributes
     * @param array $values
     * @return Model
     */
    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): Model
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }

    /**
     * @param string $parentUuid
     * @return Collection
     */
    public function findAllByFileUuid(string $parentUuid): Collection
    {
        return new Collection;
    }

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @param string $excludeUuid
     * @param string|null $oldFileUuid
     * @return Collection
     */
    public function findAllByFileableExceptUuid(string $fileableType, string $fileableId, string $excludeUuid, ?string $oldFileUuid): Collection
    {
        return new Collection;
    }

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @return string|null
     */
    public function getRootUuid(string $fileableType, string $fileableId): ?string
    {
        return null;
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function findAllWithParams(array $params): Collection
    {
        return new Collection;
    }
}
