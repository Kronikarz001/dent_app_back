<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of DepartmentServiceInterface
 */
interface DepartmentServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getDepartments(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getDepartmentsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department;

    /**
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department;

    /**
     * @param Department $department
     * @return void
     */
    public function deleteDepartment(Department $department): void;

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignRoles(Department $department, array $data): void;

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignJobPositions(Department $department, array $data): void;

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignUsers(Department $department, array $data): void;

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignPermissions(Department $department, array $data): void;

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function delegate(Department $department, array $data): void;

    /**
     * @param Department $department
     * @param array $data
     * @return Role
     */
    public function createManagedRole(Department $department, array $data): Role;
}
