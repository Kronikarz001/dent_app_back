<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserGroup;
use App\Search\Search;
use App\Search\UserGroupSearch;

/**
 * Summary of UserGroupRepository
 *
 * @extends SearchableRepository<UserGroup>
 */
class UserGroupRepository extends SearchableRepository implements UserGroupRepositoryInterface
{
    /**
     * @var class-string<UserGroup>
     */
    protected string $modelClass = UserGroup::class;

    /**
     * @param UserGroupSearch $search
     */
    public function __construct(
        private readonly UserGroupSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param UserGroup $group
     * @param User $user
     * @return bool
     */
    public function isManager(UserGroup $group, User $user): bool
    {
        return $group->users()
            ->where('users.uuid', $user->uuid)
            ->wherePivot('is_manager', true)
            ->exists();
    }
}
