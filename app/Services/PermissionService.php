<?php

namespace App\Services;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Repositories\PermissionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PermissionService
 */
readonly class PermissionService implements PermissionServiceInterface
{
    /**
     * @param PermissionRepositoryInterface $permissionRepository
     */
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissions(): LengthAwarePaginator
    {
        return $this->permissionRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionsList(): LengthAwarePaginator
    {
        return $this->permissionRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param User $user
     * @param string $resource
     * @param PermissionType $type
     * @return bool
     */
    public function hasPermission(User $user, string $resource, PermissionType $type): bool
    {
        return $this->permissionRepository->hasPermission($user, $resource, $type);
    }

    /**
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function hasPermissionGrant(User $user, Permission $permission): bool
    {
        return $this->permissionRepository->hasPermissionGrant($user, $permission);
    }

    /**
     * @param User $user
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasGroupGrant(User $user, PermissionGroup $group): bool
    {
        return $this->permissionRepository->hasGroupGrant($user, $group);
    }

    /**
     * @param User $user
     * @return string[]
     */
    public function getUserPermissionNames(User $user): array
    {
        return $this->permissionRepository->getUserPermissionNames($user);
    }

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param Permission $permission
     * @return bool
     */
    public function hasContainerPermissionGrant(string $assignableType, string $assignableUuid, Permission $permission): bool
    {
        return $this->permissionRepository->hasContainerPermissionGrant($assignableType, $assignableUuid, $permission);
    }

    /**
     * @param string $assignableType
     * @param string $assignableUuid
     * @param PermissionGroup $group
     * @return bool
     */
    public function hasContainerGroupGrant(string $assignableType, string $assignableUuid, PermissionGroup $group): bool
    {
        return $this->permissionRepository->hasContainerGroupGrant($assignableType, $assignableUuid, $group);
    }
}
