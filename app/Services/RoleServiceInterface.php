<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of RoleServiceInterface
 */
interface RoleServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getRoles(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getRolesList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role;

    /**
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role;

    /**
     * @param Role $role
     * @return void
     */
    public function deleteRole(Role $role): void;

    /**
     * @param Role $role
     * @param array $data
     * @return void
     */
    public function assignUsers(Role $role, array $data): void;

    /**
     * @param Role $role
     * @param array $data
     * @return void
     */
    public function assignPermissions(Role $role, array $data): void;

    /**
     * @param Role $role
     * @param array $data
     * @return void
     */
    public function delegate(Role $role, array $data): void;
}
