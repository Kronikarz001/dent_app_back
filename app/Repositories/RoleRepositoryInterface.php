<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of RoleRepositoryInterface
 */
interface RoleRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Role|null
     */
    public function findByUuid(string $uuid): ?Role;

    /**
     * @param array $data
     * @return Role
     */
    public function create(array $data): Role;

    /**
     * @param Role|Model $model
     * @param array $data
     * @return Role
     */
    public function update(Role|Model $model, array $data): Role;

    /**
     * @param Model|Role $model
     * @return bool
     */
    public function delete(Model|Role $model): bool;
}
