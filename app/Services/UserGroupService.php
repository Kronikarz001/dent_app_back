<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Role;
use App\Models\UserGroup;
use App\Repositories\UserGroupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of UserGroupService
 */
readonly class UserGroupService implements UserGroupServiceInterface
{
    /**
     * @param UserGroupRepositoryInterface $userGroupRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionGrantServiceInterface $permissionGrantService
     */
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private AuditServiceInterface $auditService,
        private PermissionGrantServiceInterface $permissionGrantService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getUserGroups(): LengthAwarePaginator
    {
        return $this->userGroupRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getUserGroupsList(): LengthAwarePaginator
    {
        return $this->userGroupRepository->findSelectAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return UserGroup
     */
    public function createUserGroup(array $data): UserGroup
    {
        return $this->userGroupRepository->create($data);
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return UserGroup
     */
    public function updateUserGroup(UserGroup $group, array $data): UserGroup
    {
        return $this->userGroupRepository->update($group, $data);
    }

    /**
     * @param UserGroup $group
     * @return void
     */
    public function deleteUserGroup(UserGroup $group): void
    {
        $this->userGroupRepository->delete($group);
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignUsers(UserGroup $group, array $data): void
    {
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($group, 'users', $this->userGroupRepository->syncRelation($group, 'users', $syncData));
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignPermissions(UserGroup $group, array $data): void
    {
        $this->permissionGrantService->assign($group, $data, Auth::user());
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignJobPositions(UserGroup $group, array $data): void
    {
        $this->auditService->recordSync($group, 'jobPositions', $this->userGroupRepository->syncRelation($group, 'jobPositions', $data['job_positions'] ?? []));
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return Role
     *
     * @throws PermissionDeniedException
     */
    public function createManagedRole(UserGroup $group, array $data): Role
    {
        return $this->permissionGrantService->createManagedRole($group, $data, Auth::user());
    }
}
