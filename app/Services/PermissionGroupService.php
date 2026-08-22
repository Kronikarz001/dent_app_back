<?php

namespace App\Services;

use App\Models\PermissionGroup;
use App\Repositories\PermissionGroupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PermissionGroupService
 */
readonly class PermissionGroupService implements PermissionGroupServiceInterface
{
    /**
     * @param PermissionGroupRepositoryInterface $permissionGroupRepository
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private PermissionGroupRepositoryInterface $permissionGroupRepository,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionGroups(): LengthAwarePaginator
    {
        return $this->permissionGroupRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionGroupsList(): LengthAwarePaginator
    {
        return $this->permissionGroupRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return PermissionGroup
     */
    public function createPermissionGroup(array $data): PermissionGroup
    {
        return $this->permissionGroupRepository->create($data);
    }

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return PermissionGroup
     */
    public function updatePermissionGroup(PermissionGroup $group, array $data): PermissionGroup
    {
        return $this->permissionGroupRepository->update($group, $data);
    }

    /**
     * @param PermissionGroup $group
     * @return void
     */
    public function deletePermissionGroup(PermissionGroup $group): void
    {
        $this->permissionGroupRepository->delete($group);
    }

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return void
     */
    public function assignPermissions(PermissionGroup $group, array $data): void
    {
        $this->auditService->recordSync($group, 'permissions', $group->permissions()->sync($data['permissions'] ?? []));
    }
}
