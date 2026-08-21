<?php

namespace App\Repositories;

use App\Models\PermissionGroup;
use App\Search\PermissionGroupSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PermissionGroupRepository
 */
class PermissionGroupRepository extends SearchableRepository implements PermissionGroupRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = PermissionGroup::class;

    /**
     * @param PermissionGroupSearch $search
     */
    public function __construct(
        private readonly PermissionGroupSearch $search
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
     * @return PermissionGroup|null
     */
    public function findByUuid(string $uuid): ?PermissionGroup
    {
        return PermissionGroup::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return PermissionGroup
     */
    public function create(array $data): PermissionGroup
    {
        return PermissionGroup::create($data);
    }

    /**
     * @param PermissionGroup|Model $model
     * @param array $data
     * @return PermissionGroup
     */
    public function update(PermissionGroup|Model $model, array $data): PermissionGroup
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param PermissionGroup|Model $model
     * @return bool
     */
    public function delete(PermissionGroup|Model $model): bool
    {
        return $model->delete();
    }
}
