<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of BasicRepository
 */
abstract class BasicRepository implements BasicRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass;

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $uuid
     * @return Model|null
     */
    public function findByUuid(string $uuid): ?Model
    {
        return $this->modelClass::where('uuid', $uuid)->first();
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return $this->modelClass::whereIn('uuid', $uuids)->get();
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
     * @param Model $model
     * @param string $relationMethod
     * @param array|null $desiredIds
     * @param callable|null $pivotCallback
     * @return void
     */
    protected function syncPivotRelation(Model $model, string $relationMethod, ?array $desiredIds, ?callable $pivotCallback = null): void
    {
        if ($desiredIds === null) {
            return;
        }

        /** @var BelongsToMany $relation */
        $relation = $model->{$relationMethod}();
        $pivotKey = $relation->getQualifiedRelatedPivotKeyName();
        $currentIds = $relation->pluck($pivotKey)->all();

        $toAttach = array_diff($desiredIds, $currentIds);
        $toDetach = array_diff($currentIds, $desiredIds);

        if (! empty($toDetach)) {
            $relation->detach($toDetach);
        }

        if (is_null($pivotCallback)) {
            if (! empty($toAttach)) {
                $relation->attach($toAttach);
            }

            return;
        }

        if (! empty($toAttach)) {
            $payload = [];
            foreach ($toAttach as $id) {
                $payload[$id] = $pivotCallback($id);
            }
            $relation->attach($payload);
        }
    }
}
