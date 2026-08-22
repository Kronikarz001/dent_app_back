<?php

namespace App\Services;

use App\Models\Role;
use App\Models\UserGroup;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of UserGroupServiceInterface
 */
interface UserGroupServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getUserGroups(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getUserGroupsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return UserGroup
     */
    public function createUserGroup(array $data): UserGroup;

    /**
     * @param UserGroup $group
     * @param array $data
     * @return UserGroup
     */
    public function updateUserGroup(UserGroup $group, array $data): UserGroup;

    /**
     * @param UserGroup $group
     * @return void
     */
    public function deleteUserGroup(UserGroup $group): void;

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignUsers(UserGroup $group, array $data): void;

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignPermissions(UserGroup $group, array $data): void;

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignJobPositions(UserGroup $group, array $data): void;

    /**
     * @param UserGroup $group
     * @param array $data
     * @return Role
     */
    public function createManagedRole(UserGroup $group, array $data): Role;
}
