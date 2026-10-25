<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\UserGroupRepositoryInterface;
use Illuminate\Auth\Access\Response;

/**
 * Summary of UserGroupPolicy
 */
readonly class UserGroupPolicy
{
    /**
     * @param UserGroupRepositoryInterface $userGroupRepository
     */
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
    ) {}

    /**
     * @param User $user
     * @param UserGroup $group
     * @return Response
     */
    public function manage(User $user, UserGroup $group): Response
    {
        return $this->userGroupRepository->isManager($group, $user)
            ? Response::allow()
            : Response::deny('Nie jesteś kierownikiem tej grupy użytkowników.');
    }
}
