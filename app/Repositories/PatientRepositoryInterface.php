<?php

namespace App\Repositories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PatientRepositoryInterface
 */
interface PatientRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Patient|null
     */
    public function findByUuid(string $uuid): ?Patient;

    /**
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient;

    /**
     * @param Patient|Model $model
     * @param array $data
     * @return Patient
     */
    public function update(Patient|Model $model, array $data): Patient;

    /**
     * @param Model|Patient $model
     * @return bool
     */
    public function delete(Model|Patient $model): bool;
}
