<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Auth\Access\Response;

/**
 * Summary of RolePolicy
 */
readonly class RolePolicy
{
    /**
     * @param RoleRepositoryInterface $roleRepository
     */
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @param User $user
     * @param Role $role
     * @param string $targetUuid
     * @return Response
     */
    public function delegate(User $user, Role $role, string $targetUuid): Response
    {
        if (! $this->roleRepository->isManager($role, $user)) {
            return Response::deny('Nie jesteś kierownikiem tej roli.');
        }

        return $this->roleRepository->hasMember($role, $targetUuid)
            ? Response::allow()
            : Response::deny('Docelowy użytkownik nie należy do tej roli.');
    }
}
