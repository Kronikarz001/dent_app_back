<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Role;
use App\Repositories\RoleRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of RoleService
 */
readonly class RoleService implements RoleServiceInterface
{
    /**
     * @param RoleRepositoryInterface $roleRepository
     * @param UserRepositoryInterface $userRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionGrantServiceInterface $permissionGrantService
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private UserRepositoryInterface $userRepository,
        private AuditServiceInterface $auditService,
        private PermissionGrantServiceInterface $permissionGrantService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getRoles(): LengthAwarePaginator
    {
        return $this->roleRepository->findAllWithPagination();
    }

    /**
     * @return Collection
     */
    public function getRolesList(): Collection
    {
        return $this->roleRepository->findSelectAll(['uuid', 'name']);
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

        $this->auditService->recordSync($role, 'users', $this->roleRepository->syncRelation($role, 'users', $syncData));
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
        $this->permissionGrantService->assign($role, $data, Auth::user());
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
        $this->permissionGrantService->delegate($this->userRepository->findByUuidOrFail($data['user_uuid']), $data, Auth::user());
    }
}
