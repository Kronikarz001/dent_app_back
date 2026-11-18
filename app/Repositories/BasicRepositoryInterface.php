<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of BasicRepositoryInterface
 *
 * @template TModel of Model
 */
interface BasicRepositoryInterface
{
    /**
     * @param array $params
     * @param int|null $perPage
     * @return LengthAwarePaginator<int, TModel>
     */
    public function findAllWithPagination(array $params = [], ?int $perPage = null): LengthAwarePaginator;

    /**
     * @param string[] $columns
     * @param array $params
     * @return Collection<int, TModel>
     */
    public function findSelectAll(array $columns, array $params = []): Collection;

    /**
     * @param array $params
     * @return Collection<int, TModel>
     */
    public function findAllWithoutPagination(array $params = []): Collection;

    /**
     * @param string $uuid
     * @return TModel|null
     */
    public function findByUuid(string $uuid): ?Model;

    /**
     * @param string $uuid
     * @return TModel
     */
    public function findByUuidOrFail(string $uuid): Model;

    /**
     * @param TModel $model
     * @param string[] $relations
     * @return TModel
     */
    public function loadRelations(Model $model, array $relations): Model;

    /**
     * @param Collection<int, TModel> $models
     * @param string[] $relations
     * @return Collection<int, TModel>
     */
    public function loadRelationsForMany(Collection $models, array $relations): Collection;

    /**
     * @param TModel $model
     * @param string $relation
     * @param array $ids
     * @return array{attached: array, detached: array, updated: array}
     */
    public function syncRelation(Model $model, string $relation, array $ids): array;

    /**
     * @param string[] $uuids
     * @return Collection<int, TModel>
     */
    public function findAllByUuids(array $uuids): Collection;

    /**
     * @param string $column
     * @param mixed $value
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsBy(string $column, mixed $value, ?string $ignoreUuid = null): bool;

    /**
     * @param array $data
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * @param TModel $model
     * @param array $data
     * @return TModel
     */
    public function update(Model $model, array $data): Model;

    /**
     * @param TModel $model
     * @return bool
     */
    public function delete(Model $model): bool;
}
