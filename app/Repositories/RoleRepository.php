<?php

namespace App\Repositories;

use App\Models\Role;
use App\Search\RoleSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of RoleRepository
 */
class RoleRepository extends SearchableRepository implements RoleRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Role::class;

    /**
     * @param RoleSearch $search
     */
    public function __construct(
        private readonly RoleSearch $search
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
     * @return Role|null
     */
    public function findByUuid(string $uuid): ?Role
    {
        return Role::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * @param Role|Model $model
     * @param array $data
     * @return Role
     */
    public function update(Role|Model $model, array $data): Role
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Role|Model $model
     * @return bool
     */
    public function delete(Role|Model $model): bool
    {
        return $model->delete();
    }
}
