<?php

namespace App\Repositories;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of UserGroupRepositoryInterface
 */
interface UserGroupRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return UserGroup|null
     */
    public function findByUuid(string $uuid): ?UserGroup;

    /**
     * @param array $data
     * @return UserGroup
     */
    public function create(array $data): UserGroup;

    /**
     * @param UserGroup|Model $model
     * @param array $data
     * @return UserGroup
     */
    public function update(UserGroup|Model $model, array $data): UserGroup;

    /**
     * @param Model|UserGroup $model
     * @return bool
     */
    public function delete(Model|UserGroup $model): bool;
}
