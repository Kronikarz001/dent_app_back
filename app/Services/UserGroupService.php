<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
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
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private AuditServiceInterface $auditService,
        private PermissionServiceInterface $permissionService,
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
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($group, 'users', $group->users()->sync($syncData));
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
     * @param UserGroup $group
     * @param array $data
     * @return void
     */
    public function assignJobPositions(UserGroup $group, array $data): void
    {
        $this->auditService->recordSync($group, 'jobPositions', $group->jobPositions()->sync($data['job_positions'] ?? []));
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
        /** @var User $actingUser */
        $actingUser = Auth::user();

        if (! $group->users()->where('users.uuid', $actingUser->uuid)->wherePivot('is_manager', true)->exists()) {
            throw new PermissionDeniedException('Nie jesteś kierownikiem tej grupy użytkowników.');
        }

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $permission = Permission::where('uuid', $permissionUuid)->firstOrFail();

            if (! $this->permissionService->hasContainerPermissionGrant(UserGroup::class, $group->uuid, $permission)) {
                throw new PermissionDeniedException('Uprawnienie wykracza poza uprawnienia tej grupy użytkowników.');
            }
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $permissionGroup = PermissionGroup::where('uuid', $permissionGroupUuid)->firstOrFail();

            if (! $this->permissionService->hasContainerGroupGrant(UserGroup::class, $group->uuid, $permissionGroup)) {
                throw new PermissionDeniedException('Grupa uprawnień wykracza poza uprawnienia tej grupy użytkowników.');
            }
        }

        $role = Role::create(['name' => $data['name']]);
        $group->roles()->attach($role->uuid);

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $this->createGrantForRole($permissionUuid, Permission::class, $role, $actingUser);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $this->createGrantForRole($permissionGroupUuid, PermissionGroup::class, $role, $actingUser);
        }

        return $role;
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

    /**
     * @param string $grantableUuid
     * @param string $grantableType
     * @param Role $role
     * @param User $grantedBy
     * @return void
     */
    private function createGrantForRole(string $grantableUuid, string $grantableType, Role $role, User $grantedBy): void
    {
        PermissionAssignment::create([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => Role::class,
            'assignable_id' => $role->uuid,
            'granted_by' => $grantedBy->uuid,
        ]);
    }
}
