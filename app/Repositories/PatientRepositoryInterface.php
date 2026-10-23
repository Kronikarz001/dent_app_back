<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * @return LengthAwarePaginator
     */
    public function findAllWithRomexisId(): LengthAwarePaginator;

    /**
     * @param Patient $patient
     * @return User|null
     */
    public function findDoctor(Patient $patient): ?User;

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
