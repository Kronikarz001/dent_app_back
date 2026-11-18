<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\User;

/**
 * Summary of DepartmentRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Department>
 */
interface DepartmentRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param Department $department
     * @param User $user
     * @return bool
     */
    public function isManager(Department $department, User $user): bool;

    /**
     * @param Department $department
     * @param string $userUuid
     * @return bool
     */
    public function hasMember(Department $department, string $userUuid): bool;
}
