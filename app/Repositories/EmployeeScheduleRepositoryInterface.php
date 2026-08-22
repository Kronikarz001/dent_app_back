<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of EmployeeScheduleRepositoryInterface
 */
interface EmployeeScheduleRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return EmployeeSchedule|null
     */
    public function findByUuid(string $uuid): ?EmployeeSchedule;

    /**
     * @param array $data
     * @return EmployeeSchedule
     */
    public function create(array $data): EmployeeSchedule;

    /**
     * @param EmployeeSchedule|Model $model
     * @param array $data
     * @return EmployeeSchedule
     */
    public function update(EmployeeSchedule|Model $model, array $data): EmployeeSchedule;

    /**
     * @param Model|EmployeeSchedule $model
     * @return bool
     */
    public function delete(Model|EmployeeSchedule $model): bool;
}
