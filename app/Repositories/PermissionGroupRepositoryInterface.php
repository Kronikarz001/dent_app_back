<?php

namespace App\Repositories;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PermissionGroupRepositoryInterface
 */
interface PermissionGroupRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return PermissionGroup|null
     */
    public function findByUuid(string $uuid): ?PermissionGroup;

    /**
     * @param array $data
     * @return PermissionGroup
     */
    public function create(array $data): PermissionGroup;

    /**
     * @param PermissionGroup|Model $model
     * @param array $data
     * @return PermissionGroup
     */
    public function update(PermissionGroup|Model $model, array $data): PermissionGroup;

    /**
     * @param Model|PermissionGroup $model
     * @return bool
     */
    public function delete(Model|PermissionGroup $model): bool;
}
