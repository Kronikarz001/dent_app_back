<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Collection;
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
     * @return Collection
     */
    public function getPermissionGroupsList(): Collection;

    /**
     * @param PermissionGroup $group
     * @return PermissionGroup
     */
    public function getPermissionGroup(PermissionGroup $group): PermissionGroup;

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

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignUsers(PermissionGroup $group, array $data): void;
}
