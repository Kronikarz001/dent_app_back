<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Repositories\DepartmentRepositoryInterface;
use Illuminate\Auth\Access\Response;

/**
 * Summary of DepartmentPolicy
 */
readonly class DepartmentPolicy
{
    /**
     * @param DepartmentRepositoryInterface $departmentRepository
     */
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
    ) {}

    /**
     * @param User $user
     * @param Department $department
     * @return Response
     */
    public function manage(User $user, Department $department): Response
    {
        return $this->departmentRepository->isManager($department, $user)
            ? Response::allow()
            : Response::deny('Nie jesteś kierownikiem tego działu.');
    }

    /**
     * @param User $user
     * @param Department $department
     * @param string $targetUuid
     * @return Response
     */
    public function delegate(User $user, Department $department, string $targetUuid): Response
    {
        $manage = $this->manage($user, $department);

        if ($manage->denied()) {
            return $manage;
        }

        return $this->departmentRepository->hasMember($department, $targetUuid)
            ? Response::allow()
            : Response::deny('Docelowy użytkownik nie należy do tego działu.');
    }
}
