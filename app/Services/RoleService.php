<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of RoleService
 */
readonly class RoleService implements RoleServiceInterface
{
    /**
     * @param RoleRepositoryInterface $roleRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuditServiceInterface $auditService,
        private PermissionServiceInterface $permissionService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getRoles(): LengthAwarePaginator
    {
        return $this->roleRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getRolesList(): LengthAwarePaginator
    {
        return $this->roleRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Role
     */
    public function createRole(array $data): Role
    {
        return $this->roleRepository->create($data);
    }

    /**
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function updateRole(Role $role, array $data): Role
    {
        return $this->roleRepository->update($role, $data);
    }

    /**
     * @param Role $role
     * @return void
     */
    public function deleteRole(Role $role): void
    {
        $this->roleRepository->delete($role);
    }

    /**
     * @param Role $role
     * @param array $data
     * @return void
     */
    public function assignUsers(Role $role, array $data): void
    {
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($role, 'users', $role->users()->sync($syncData));
    }

    /**
     * @param Role $role
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignPermissions(Role $role, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $permission = Permission::where('uuid', $permissionUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasPermissionGrant($actingUser, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go nadać.');
            }

            $this->grant(Permission::class, $permission->uuid, $role, $data['expires_at'] ?? null);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $group = PermissionGroup::where('uuid', $permissionGroupUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasGroupGrant($actingUser, $group)) {
                throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej nadać.');
            }

            $this->grant(PermissionGroup::class, $group->uuid, $role, $data['expires_at'] ?? null);
        }
    }

    /**
     * @param Role $role
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(Role $role, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        if (! $role->users()->where('users.uuid', $actingUser->uuid)->wherePivot('is_manager', true)->exists()) {
            throw new PermissionDeniedException('Nie jesteś kierownikiem tej roli.');
        }

        if (! $role->users()->where('users.uuid', $data['user_uuid'])->exists()) {
            throw new PermissionDeniedException('Docelowy użytkownik nie należy do tej roli.');
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
     * @param string $grantableType
     * @param string $grantableUuid
     * @param Role $role
     * @param string|null $expiresAt
     * @return void
     */
    private function grant(string $grantableType, string $grantableUuid, Role $role, ?string $expiresAt): void
    {
        PermissionAssignment::firstOrCreate([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => Role::class,
            'assignable_id' => $role->uuid,
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
