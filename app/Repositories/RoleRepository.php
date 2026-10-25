<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Search\RoleSearch;
use App\Search\Search;

/**
 * Summary of RoleRepository
 *
 * @extends SearchableRepository<Role>
 */
class RoleRepository extends SearchableRepository implements RoleRepositoryInterface
{
    /**
     * @var class-string<Role>
     */
    protected string $modelClass = Role::class;

    /**
     * @param RoleSearch $search
     */
    public function __construct(
        private readonly RoleSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param Role $role
     * @param User $user
     * @return bool
     */
    public function isManager(Role $role, User $user): bool
    {
        return $role->users()
            ->where('users.uuid', $user->uuid)
            ->wherePivot('is_manager', true)
            ->exists();
    }

    /**
     * @param Role $role
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(Role $role, string $userUuid): bool
    {
        return $role->users()->where('users.uuid', $userUuid)->exists();
    }

    /**
     * @param Department|UserGroup $container
     * @param string $name
     * @return Role
     */
    public function createInContainer(Department|UserGroup $container, string $name): Role
    {
        $role = $this->create(['name' => $name]);
        $container->roles()->attach($role->uuid);

        return $role;
    }
}
