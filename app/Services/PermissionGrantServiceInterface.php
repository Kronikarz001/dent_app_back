<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Department;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PermissionGrantServiceInterface
 */
interface PermissionGrantServiceInterface
{
    /**
     * @param Model $assignable
     * @param array $data
     * @param User $actor
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assign(Model $assignable, array $data, User $actor): void;

    /**
     * @param User $target
     * @param array $data
     * @param User $actor
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(User $target, array $data, User $actor): void;

    /**
     * @param PermissionGroup $group
     * @param string[] $userUuids
     * @param User $actor
     * @return array{attached: string[], detached: string[]}
     *
     * @throws PermissionDeniedException
     */
    public function syncGroupUsers(PermissionGroup $group, array $userUuids, User $actor): array;

    /**
     * @param Department|UserGroup $container
     * @param array $data
     * @param User $actor
     * @return Role
     *
     * @throws PermissionDeniedException
     */
    public function createManagedRole(Department|UserGroup $container, array $data, User $actor): Role;
}
