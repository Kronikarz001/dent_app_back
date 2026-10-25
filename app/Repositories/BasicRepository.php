<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of BasicRepository
 *
 * @template TModel of Model
 *
 * @implements BasicRepositoryInterface<TModel>
 */
abstract class BasicRepository implements BasicRepositoryInterface
{
    /**
     * @var class-string<TModel>
     */
    protected string $modelClass;

    /**
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * @param array $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * @param TModel $model
     * @param array $data
     * @return TModel
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param TModel $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $uuid
     * @return TModel|null
     */
    public function findByUuid(string $uuid): ?Model
    {
        return $this->query()->where('uuid', $uuid)->first();
    }

    /**
     * @param string $uuid
     * @return TModel
     */
    public function findByUuidOrFail(string $uuid): Model
    {
        return $this->query()->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * @param TModel $model
     * @param string[] $relations
     * @return TModel
     */
    public function loadRelations(Model $model, array $relations): Model
    {
        return $model->load($relations);
    }

    /**
     * @param TModel $model
     * @param string $relation
     * @param array $ids
     * @return array{attached: array, detached: array, updated: array}
     */
    public function syncRelation(Model $model, string $relation, array $ids): array
    {
        return $model->{$relation}()->sync($ids);
    }

    /**
     * @param string[] $uuids
     * @return Collection<int, TModel>
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return $this->query()->whereIn('uuid', $uuids)->get();
    }

    /**
     * @param string $column
     * @param mixed $value
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsBy(string $column, mixed $value, ?string $ignoreUuid = null): bool
    {
        return $this->query()
            ->where($column, $value)
            ->when($ignoreUuid, fn (Builder $query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }
}
