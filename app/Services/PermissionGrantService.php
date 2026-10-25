<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Department;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\PermissionGroupRepositoryInterface;
use App\Repositories\PermissionRepositoryInterface;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PermissionGrantService
 */
readonly class PermissionGrantService implements PermissionGrantServiceInterface
{
    /**
     * @param PermissionRepositoryInterface $permissionRepository
     * @param PermissionGroupRepositoryInterface $permissionGroupRepository
     * @param RoleRepositoryInterface $roleRepository
     */
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private PermissionGroupRepositoryInterface $permissionGroupRepository,
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @param Model $assignable
     * @param array $data
     * @param User $actor
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assign(Model $assignable, array $data, User $actor): void
    {
        foreach ($this->findPermissions($data['permissions'] ?? []) as $permission) {
            if (! $actor->is_admin && ! $this->permissionRepository->hasPermissionGrant($actor, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go nadać.');
            }

            $this->permissionRepository->grant($permission, $assignable, $data['expires_at'] ?? null, $actor->uuid);
        }

        foreach ($this->findPermissionGroups($data['permission_groups'] ?? []) as $group) {
            if (! $actor->is_admin && ! $this->permissionRepository->hasGroupGrant($actor, $group)) {
                throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej nadać.');
            }

            $this->permissionRepository->grant($group, $assignable, $data['expires_at'] ?? null, $actor->uuid);
        }
    }

    /**
     * @param User $target
     * @param array $data
     * @param User $actor
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(User $target, array $data, User $actor): void
    {
        if (! empty($data['permission_uuid'])) {
            $permission = $this->permissionRepository->findByUuidOrFail($data['permission_uuid']);

            if (! $this->permissionRepository->hasPermissionGrant($actor, $permission)) {
                throw new PermissionDeniedException('Nie posiadasz tego uprawnienia, nie możesz go przekazać.');
            }

            $this->permissionRepository->createGrant($permission, $target, $data['expires_at'] ?? null, $actor->uuid);

            return;
        }

        $group = $this->permissionGroupRepository->findByUuidOrFail($data['permission_group_uuid']);

        if (! $this->permissionRepository->hasGroupGrant($actor, $group)) {
            throw new PermissionDeniedException('Nie posiadasz tej grupy uprawnień, nie możesz jej przekazać.');
        }

        $this->permissionRepository->createGrant($group, $target, $data['expires_at'] ?? null, $actor->uuid);
    }

    /**
     * @param Department|UserGroup $container
     * @param array $data
     * @param User $actor
     * @return Role
     *
     * @throws PermissionDeniedException
     */
    public function createManagedRole(Department|UserGroup $container, array $data, User $actor): Role
    {
        $permissions = $this->findPermissions($data['permissions'] ?? []);
        $groups = $this->findPermissionGroups($data['permission_groups'] ?? []);

        foreach ($permissions as $permission) {
            if (! $this->permissionRepository->hasContainerPermissionGrant($container::class, $container->uuid, $permission)) {
                throw new PermissionDeniedException('Uprawnienie wykracza poza uprawnienia kontenera, w którym tworzysz rolę.');
            }
        }

        foreach ($groups as $group) {
            if (! $this->permissionRepository->hasContainerGroupGrant($container::class, $container->uuid, $group)) {
                throw new PermissionDeniedException('Grupa uprawnień wykracza poza uprawnienia kontenera, w którym tworzysz rolę.');
            }
        }

        $role = $this->roleRepository->createInContainer($container, $data['name']);

        foreach ([...$permissions, ...$groups] as $grantable) {
            $this->permissionRepository->createGrant($grantable, $role, null, $actor->uuid);
        }

        return $role;
    }

    /**
     * @param string[] $uuids
     * @return Permission[]
     */
    private function findPermissions(array $uuids): array
    {
        return array_map(fn (string $uuid) => $this->permissionRepository->findByUuidOrFail($uuid), $uuids);
    }

    /**
     * @param string[] $uuids
     * @return PermissionGroup[]
     */
    private function findPermissionGroups(array $uuids): array
    {
        return array_map(fn (string $uuid) => $this->permissionGroupRepository->findByUuidOrFail($uuid), $uuids);
    }
}
