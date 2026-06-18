<?php

namespace App\Repositories;

use App\Models\DentalExamination;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DentalExaminationRepositoryInterface
 */
interface DentalExaminationRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return DentalExamination|null
     */
    public function findByUuid(string $uuid): ?DentalExamination;

    /**
     * @param array $data
     * @return DentalExamination
     */
    public function create(array $data): DentalExamination;

    /**
     * @param DentalExamination|Model $model
     * @param array $data
     * @return DentalExamination
     */
    public function update(DentalExamination|Model $model, array $data): DentalExamination;

    /**
     * @param Model|DentalExamination $model
     * @return bool
     */
    public function delete(Model|DentalExamination $model): bool;
}
