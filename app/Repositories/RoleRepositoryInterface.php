<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;

/**
 * Summary of RoleRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Role>
 */
interface RoleRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param Role $role
     * @param User $user
     * @return bool
     */
    public function isManager(Role $role, User $user): bool;

    /**
     * @param Role $role
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(Role $role, string $userUuid): bool;

    /**
     * @param Department|UserGroup $container
     * @param string $name
     * @return Role
     */
    public function createInContainer(Department|UserGroup $container, string $name): Role;
}
