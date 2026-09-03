<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Department;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Repositories\DepartmentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of DepartmentService
 */
readonly class DepartmentService implements DepartmentServiceInterface
{
    /**
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionServiceInterface $permissionService
     */
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private AuditServiceInterface $auditService,
        private PermissionServiceInterface $permissionService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getDepartments(): LengthAwarePaginator
    {
        return $this->departmentRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getDepartmentsList(): LengthAwarePaginator
    {
        return $this->departmentRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return $this->departmentRepository->create($data);
    }

    /**
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return $this->departmentRepository->update($department, $data);
    }

    /**
     * @param Department $department
     * @return void
     */
    public function deleteDepartment(Department $department): void
    {
        $this->departmentRepository->delete($department);
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignRoles(Department $department, array $data): void
    {
        $this->auditService->recordSync($department, 'roles', $department->roles()->sync($data['roles'] ?? []));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignJobPositions(Department $department, array $data): void
    {
        $this->auditService->recordSync($department, 'jobPositions', $department->jobPositions()->sync($data['job_positions'] ?? []));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignUsers(Department $department, array $data): void
    {
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($department, 'users', $department->users()->sync($syncData));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignPermissions(Department $department, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $permission = Permission::where('uuid', $permissionUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasPermissionGrant($actingUser, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go nadać.');
            }

            $this->grant(Permission::class, $permission->uuid, $department, $data['expires_at'] ?? null);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $group = PermissionGroup::where('uuid', $permissionGroupUuid)->firstOrFail();

            if (! $actingUser->is_admin && ! $this->permissionService->hasGroupGrant($actingUser, $group)) {
                throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej nadać.');
            }

            $this->grant(PermissionGroup::class, $group->uuid, $department, $data['expires_at'] ?? null);
        }
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(Department $department, array $data): void
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        $this->assertIsManager($department, $actingUser);

        if (! $this->isMember($department, $data['user_uuid'])) {
            throw new PermissionDeniedException('Docelowy użytkownik nie należy do tego działu.');
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
     * @param Department $department
     * @param array $data
     * @return Role
     *
     * @throws PermissionDeniedException
     */
    public function createManagedRole(Department $department, array $data): Role
    {
        /** @var User $actingUser */
        $actingUser = Auth::user();

        $this->assertIsManager($department, $actingUser);

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $permission = Permission::where('uuid', $permissionUuid)->firstOrFail();

            if (! $this->permissionService->hasContainerPermissionGrant(Department::class, $department->uuid, $permission)) {
                throw new PermissionDeniedException('Uprawnienie wykracza poza uprawnienia tego działu.');
            }
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $group = PermissionGroup::where('uuid', $permissionGroupUuid)->firstOrFail();

            if (! $this->permissionService->hasContainerGroupGrant(Department::class, $department->uuid, $group)) {
                throw new PermissionDeniedException('Grupa uprawnień wykracza poza uprawnienia tego działu.');
            }
        }

        $role = Role::create(['name' => $data['name']]);
        $department->roles()->attach($role->uuid);

        foreach ($data['permissions'] ?? [] as $permissionUuid) {
            $this->createGrantForRole($permissionUuid, Permission::class, $role, $actingUser);
        }

        foreach ($data['permission_groups'] ?? [] as $permissionGroupUuid) {
            $this->createGrantForRole($permissionGroupUuid, PermissionGroup::class, $role, $actingUser);
        }

        return $role;
    }

    /**
     * A department manager is either a user directly attached to the
     * department with is_manager=true, or a manager of a role that is
     * itself attached to the department — matching isMember()'s notion of
     * "belongs to this department" so the two never disagree on the same
     * user.
     *
     * @param Department $department
     * @param User $user
     * @return void
     *
     * @throws PermissionDeniedException
     */
    private function assertIsManager(Department $department, User $user): void
    {
        $isDirectManager = $department->users()->where('users.uuid', $user->uuid)->wherePivot('is_manager', true)->exists();

        $isManagerViaRole = $department->roles()
            ->whereHas('users', fn ($query) => $query->where('users.uuid', $user->uuid)->where('role_user.is_manager', true))
            ->exists();

        if (! $isDirectManager && ! $isManagerViaRole) {
            throw new PermissionDeniedException('Nie jesteś kierownikiem tego działu.');
        }
    }

    /**
     * @param Department $department
     * @param string $userUuid
     * @return bool
     */
    private function isMember(Department $department, string $userUuid): bool
    {
        if ($department->users()->where('users.uuid', $userUuid)->exists()) {
            return true;
        }

        return $department->roles()
            ->whereHas('users', fn ($query) => $query->where('users.uuid', $userUuid))
            ->exists();
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param Department $department
     * @param string|null $expiresAt
     * @return void
     */
    private function grant(string $grantableType, string $grantableUuid, Department $department, ?string $expiresAt): void
    {
        PermissionAssignment::firstOrCreate([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => Department::class,
            'assignable_id' => $department->uuid,
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
