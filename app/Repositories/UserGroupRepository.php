<?php

namespace App\Repositories;

use App\Models\UserGroup;
use App\Search\Search;
use App\Search\UserGroupSearch;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of UserGroupRepository
 */
class UserGroupRepository extends SearchableRepository implements UserGroupRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = UserGroup::class;

    /**
     * @param UserGroupSearch $search
     */
    public function __construct(
        private readonly UserGroupSearch $search
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
     * @return UserGroup|null
     */
    public function findByUuid(string $uuid): ?UserGroup
    {
        return UserGroup::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return UserGroup
     */
    public function create(array $data): UserGroup
    {
        return UserGroup::create($data);
    }

    /**
     * @param UserGroup|Model $model
     * @param array $data
     * @return UserGroup
     */
    public function update(UserGroup|Model $model, array $data): UserGroup
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param UserGroup|Model $model
     * @return bool
     */
    public function delete(UserGroup|Model $model): bool
    {
        return $model->delete();
    }
}
