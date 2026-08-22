<?php

namespace App\Repositories;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;

/**
 * Summary of PermissionRepositoryInterface
 */
interface PermissionRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Permission|null
     */
    public function findByUuid(string $uuid): ?Permission;

    /**
     * @param User $user
     * @param string $resource
     * @param PermissionType $type
     * @return bool
     */
    public function hasPermission(User $user, string $resource, PermissionType $type): bool;

    /**
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function hasPermissionGrant(User $user, Permission $permission): bool;

    /**
     * @param User $user
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasGroupGrant(User $user, PermissionGroup $group): bool;

    /**
     * @param User $user
     * @return string[]
     */
    public function getUserPermissionNames(User $user): array;

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param Permission $permission
     * @return bool
     */
    public function hasContainerPermissionGrant(string $assignableType, string $assignableUuid, Permission $permission): bool;

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasContainerGroupGrant(string $assignableType, string $assignableUuid, PermissionGroup $group): bool;
}
