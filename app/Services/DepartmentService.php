<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Department;
use App\Models\Role;
use App\Repositories\DepartmentRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of DepartmentService
 */
readonly class DepartmentService implements DepartmentServiceInterface
{
    /**
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param UserRepositoryInterface $userRepository
     * @param AuditServiceInterface $auditService
     * @param PermissionGrantServiceInterface $permissionGrantService
     */
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private UserRepositoryInterface $userRepository,
        private AuditServiceInterface $auditService,
        private PermissionGrantServiceInterface $permissionGrantService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getDepartments(): LengthAwarePaginator
    {
        return $this->departmentRepository->findAllWithPagination();
    }

    /**
     * @return Collection
     */
    public function getDepartmentsList(): Collection
    {
        return $this->departmentRepository->findSelectAll(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return $this->departmentRepository->create($data);
    }

    /**
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return $this->departmentRepository->update($department, $data);
    }

    /**
     * @param Department $department
     * @return void
     */
    public function deleteDepartment(Department $department): void
    {
        $this->departmentRepository->delete($department);
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignRoles(Department $department, array $data): void
    {
        $this->auditService->recordSync($department, 'roles', $this->departmentRepository->syncRelation($department, 'roles', $data['roles'] ?? []));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignJobPositions(Department $department, array $data): void
    {
        $this->auditService->recordSync($department, 'jobPositions', $this->departmentRepository->syncRelation($department, 'jobPositions', $data['job_positions'] ?? []));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     */
    public function assignUsers(Department $department, array $data): void
    {
        $syncData = collect($data['users'] ?? [])
            ->mapWithKeys(fn (array $entry) => [$entry['uuid'] => ['is_manager' => $entry['is_manager'] ?? false]])
            ->all();

        $this->auditService->recordSync($department, 'users', $this->departmentRepository->syncRelation($department, 'users', $syncData));
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function assignPermissions(Department $department, array $data): void
    {
        $this->permissionGrantService->assign($department, $data, Auth::user());
    }

    /**
     * @param Department $department
     * @param array $data
     * @return void
     *
     * @throws PermissionDeniedException
     */
    public function delegate(Department $department, array $data): void
    {
        $this->permissionGrantService->delegate($this->userRepository->findByUuidOrFail($data['user_uuid']), $data, Auth::user());
    }

    /**
     * @param Department $department
     * @param array $data
     * @return Role
     *
     * @throws PermissionDeniedException
     */
    public function createManagedRole(Department $department, array $data): Role
    {
        return $this->permissionGrantService->createManagedRole($department, $data, Auth::user());
    }
}
