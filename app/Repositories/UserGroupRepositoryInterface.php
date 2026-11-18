<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserGroup;

/**
 * Summary of UserGroupRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<UserGroup>
 */
interface UserGroupRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param UserGroup $group
     * @param User $user
     * @return bool
     */
    public function isManager(UserGroup $group, User $user): bool;
}
