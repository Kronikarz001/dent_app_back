<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\RoleGroup;
use App\Models\User;
use App\Repositories\RoleGroupRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of RoleGroupService
 */
readonly class RoleGroupService implements RoleGroupServiceInterface
{
    /**
     * @param RoleGroupRepositoryInterface $roleGroupRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private RoleGroupRepositoryInterface $roleGroupRepository,
        private AuditServiceInterface $auditService,
        private PermissionServiceInterface $permissionService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getRoleGroups(): LengthAwarePaginator
    {
        return $this->roleGroupRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getRoleGroupsList(): LengthAwarePaginator
    {
        return $this->roleGroupRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return RoleGroup
     */
    public function createRoleGroup(array $data): RoleGroup
    {
        return $this->roleGroupRepository->create($data);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return RoleGroup
     */
    public function updateRoleGroup(RoleGroup $roleGroup, array $data): RoleGroup
    {
        return $this->roleGroupRepository->update($roleGroup, $data);
    }

    /**
     * @param RoleGroup $roleGroup
     * @return void
     */
    public function deleteRoleGroup(RoleGroup $roleGroup): void
    {
        $this->roleGroupRepository->delete($roleGroup);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignRoles(RoleGroup $roleGroup, array $data): void
    {
        $this->auditService->recordSync($roleGroup, 'roles', $roleGroup->roles()->sync($data['roles'] ?? []));
    }

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignUsers(RoleGroup $roleGroup, array $data): void
    {
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($roleGroup, 'users', $roleGroup->users()->sync($syncData));
    }

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     */
    public function assignPermissions(RoleGroup $roleGroup, array $data): void
    {
        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $this->grant(Permission::class, $permissionUuid, $roleGroup, $data['expires_at'] ?? null);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $this->grant(PermissionGroup::class, $permissionGroupUuid, $roleGroup, $data['expires_at'] ?? null);
        }
    }

    /**
     * @param RoleGroup $roleGroup
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(RoleGroup $roleGroup, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        if (! $roleGroup->users()->where('users.uuid', $actingUser->uuid)->wherePivot('is_manager', true)->exists()) {
            throw new PermissionDeniedException('Nie jesteś kierownikiem tej grupy ról.');
        }

        if (! $this->isMember($roleGroup, $data['user_uuid'])) {
            throw new PermissionDeniedException('Docelowy użytkownik nie należy do tej grupy ról.');
        }

        $targetUser = User::where('uuid', $data['user_uuid'])->firstOrFail();

        if (! empty($data['permission_uuid'])) {
            $permission = Permission::where('uuid', $data['permission_uuid'])->firstOrFail();

            if (! $this->permissionService->hasPermissionGrant($actingUser, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go przekazać.');
            }

            $this->createGrant(Permission::class, $permission->uuid, $targetUser, $actingUser, $data['expires_at'] ?? null);

            return;
        }

        $group = PermissionGroup::where('uuid', $data['permission_group_uuid'])->firstOrFail();

        if (! $this->permissionService->hasGroupGrant($actingUser, $group)) {
            throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej przekazać.');
        }

        $this->createGrant(PermissionGroup::class, $group->uuid, $targetUser, $actingUser, $data['expires_at'] ?? null);
    }

    /**
     * @param RoleGroup $roleGroup
     * @param string $userUuid
     * @return bool
     */
    private function isMember(RoleGroup $roleGroup, string $userUuid): bool
    {
        if ($roleGroup->users()->where('users.uuid', $userUuid)->exists()) {
            return true;
        }

        return $roleGroup->roles()
            ->whereHas('users', fn ($query) => $query->where('users.uuid', $userUuid))
            ->exists();
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param RoleGroup $roleGroup
     * @param string|null $expiresAt
     * @return void
     */
    private function grant(string $grantableType, string $grantableUuid, RoleGroup $roleGroup, ?string $expiresAt): void
    {
        PermissionAssignment::firstOrCreate([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => RoleGroup::class,
            'assignable_id' => $roleGroup->uuid,
        ], [
            'expires_at' => $expiresAt,
            'granted_by' => Auth::id(),
        ]);
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param User $target
     * @param User $grantedBy
     * @param string|null $expiresAt
     * @return void
     */
    private function createGrant(string $grantableType, string $grantableUuid, User $target, User $grantedBy, ?string $expiresAt): void
    {
        PermissionAssignment::create([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => User::class,
            'assignable_id' => $target->uuid,
            'granted_by' => $grantedBy->uuid,
            'expires_at' => $expiresAt,
        ]);
    }
}
