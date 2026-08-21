<?php

namespace App\Repositories;

use App\Models\RoleGroup;
use App\Search\RoleGroupSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of RoleGroupRepository
 */
class RoleGroupRepository extends SearchableRepository implements RoleGroupRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = RoleGroup::class;

    /**
     * @param RoleGroupSearch $search
     */
    public function __construct(
        private readonly RoleGroupSearch $search
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
     * @return RoleGroup|null
     */
    public function findByUuid(string $uuid): ?RoleGroup
    {
        return RoleGroup::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return RoleGroup
     */
    public function create(array $data): RoleGroup
    {
        return RoleGroup::create($data);
    }

    /**
     * @param RoleGroup|Model $model
     * @param array $data
     * @return RoleGroup
     */
    public function update(RoleGroup|Model $model, array $data): RoleGroup
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param RoleGroup|Model $model
     * @return bool
     */
    public function delete(RoleGroup|Model $model): bool
    {
        return $model->delete();
    }
}
