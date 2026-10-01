<?php

namespace App\Repositories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MedicineRepositoryInterface
 */
interface MedicineRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Medicine|null
     */
    public function findByUuid(string $uuid): ?Medicine;

    /**
     * @param array $data
     * @return Medicine
     */
    public function create(array $data): Medicine;

    /**
     * @param Medicine|Model $model
     * @param array $data
     * @return Medicine
     */
    public function update(Medicine|Model $model, array $data): Medicine;

    /**
     * @param Model|Medicine $model
     * @return bool
     */
    public function delete(Model|Medicine $model): bool;
}
