<?php

namespace App\Repositories;

use App\Models\Prescription;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PrescriptionRepositoryInterface
 */
interface PrescriptionRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Prescription|null
     */
    public function findByUuid(string $uuid): ?Prescription;

    /**
     * @param array $data
     * @return Prescription
     */
    public function create(array $data): Prescription;

    /**
     * @param Prescription|Model $model
     * @param array $data
     * @return Prescription
     */
    public function update(Prescription|Model $model, array $data): Prescription;

    /**
     * @param Model|Prescription $model
     * @return bool
     */
    public function delete(Model|Prescription $model): bool;
}
