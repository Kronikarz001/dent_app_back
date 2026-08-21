<?php

namespace App\Services;

use App\Models\PermissionGroup;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PermissionGroupServiceInterface
 */
interface PermissionGroupServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionGroups(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionGroupsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return PermissionGroup
     */
    public function createPermissionGroup(array $data): PermissionGroup;

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return PermissionGroup
     */
    public function updatePermissionGroup(PermissionGroup $group, array $data): PermissionGroup;

    /**
     * @param PermissionGroup $group
     * @return void
     */
    public function deletePermissionGroup(PermissionGroup $group): void;

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return void
     */
    public function assignPermissions(PermissionGroup $group, array $data): void;
}
