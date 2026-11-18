<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\PermissionGroup;
use App\Repositories\PermissionGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of PermissionGroupService
 */
readonly class PermissionGroupService implements PermissionGroupServiceInterface
{
    /**
     * @param PermissionGroupRepositoryInterface $permissionGroupRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionGrantServiceInterface $permissionGrantService
     */
    public function __construct(
        private PermissionGroupRepositoryInterface $permissionGroupRepository,
        private AuditServiceInterface $auditService,
        private PermissionGrantServiceInterface $permissionGrantService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getPermissionGroups(): LengthAwarePaginator
    {
        return $this->permissionGroupRepository->findAllWithPagination();
    }

    /**
     * @return Collection
     */
    public function getPermissionGroupsList(): Collection
    {
        return $this->permissionGroupRepository->findSelectAll(['uuid', 'name']);
    }

    /**
     * @param PermissionGroup $group
     * @return PermissionGroup
     */
    public function getPermissionGroup(PermissionGroup $group): PermissionGroup
    {
        return $this->permissionGroupRepository->loadRelations($group, ['permissions', 'users:uuid,first_name,last_name']);
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
        $this->auditService->recordSync($group, 'permissions', $this->permissionGroupRepository->syncRelation($group, 'permissions', $data['permissions'] ?? []));
    }

    /**
     * @param PermissionGroup $group
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignUsers(PermissionGroup $group, array $data): void
    {
        $this->auditService->recordSync($group, 'users', $this->permissionGrantService->syncGroupUsers($group, $data['users'] ?? [], Auth::user()));
    }
}
