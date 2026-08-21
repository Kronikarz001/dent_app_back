<?php

namespace App\Repositories;

use App\Models\RoleGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of RoleGroupRepositoryInterface
 */
interface RoleGroupRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return RoleGroup|null
     */
    public function findByUuid(string $uuid): ?RoleGroup;

    /**
     * @param array $data
     * @return RoleGroup
     */
    public function create(array $data): RoleGroup;

    /**
     * @param RoleGroup|Model $model
     * @param array $data
     * @return RoleGroup
     */
    public function update(RoleGroup|Model $model, array $data): RoleGroup;

    /**
     * @param Model|RoleGroup $model
     * @return bool
     */
    public function delete(Model|RoleGroup $model): bool;
}
