<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DepartmentRepositoryInterface
 */
interface DepartmentRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Department|null
     */
    public function findByUuid(string $uuid): ?Department;

    /**
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department;

    /**
     * @param Department|Model $model
     * @param array $data
     * @return Department
     */
    public function update(Department|Model $model, array $data): Department;

    /**
     * @param Model|Department $model
     * @return bool
     */
    public function delete(Model|Department $model): bool;
}
