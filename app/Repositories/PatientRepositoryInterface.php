<?php

namespace App\Repositories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PatientRepositoryInterface
 */
interface PatientRepositoryInterface extends BasicRepositoryInterface
{
    public function findByUuid(string $uuid): ?Patient;

    public function create(array $data): Patient;

    public function update(Patient|Model $model, array $data): Patient;

    public function delete(Model|Patient $model): bool;
}
