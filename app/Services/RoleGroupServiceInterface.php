<?php

namespace App\Services;

use App\Models\RoleGroup;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of RoleGroupServiceInterface
 */
interface RoleGroupServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getRoleGroups(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getRoleGroupsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return RoleGroup
     */
    public function createRoleGroup(array $data): RoleGroup;

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return RoleGroup
     */
    public function updateRoleGroup(RoleGroup $roleGroup, array $data): RoleGroup;

    /**
     * @param RoleGroup $roleGroup
     * @return void
     */
    public function deleteRoleGroup(RoleGroup $roleGroup): void;

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignRoles(RoleGroup $roleGroup, array $data): void;

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignUsers(RoleGroup $roleGroup, array $data): void;

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignPermissions(RoleGroup $roleGroup, array $data): void;

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function delegate(RoleGroup $roleGroup, array $data): void;
}
