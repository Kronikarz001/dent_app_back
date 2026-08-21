<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
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
     */
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private AuditServiceInterface $auditService,
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
        return $this->userGroupRepository->findAllWithPagination(['uuid', 'name']);
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
        $this->auditService->recordSync($group, 'users', $group->users()->sync($data['users'] ?? []));
    }

    /**
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignPermissions(UserGroup $group, array $data): void
    {
        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $this->grant(Permission::class, $permissionUuid, $group, $data['expires_at'] ?? null);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $this->grant(PermissionGroup::class, $permissionGroupUuid, $group, $data['expires_at'] ?? null);
        }
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param UserGroup $group
     * @param string|null $expiresAt
     * @return void
     */
    private function grant(string $grantableType, string $grantableUuid, UserGroup $group, ?string $expiresAt): void
    {
        PermissionAssignment::firstOrCreate([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => UserGroup::class,
            'assignable_id' => $group->uuid,
        ], [
            'expires_at' => $expiresAt,
            'granted_by' => Auth::id(),
        ]);
    }
}
