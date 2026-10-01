<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of MedicineServiceInterface
 */
interface MedicineServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getMedicines(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getMedicinesList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Medicine
     */
    public function createMedicine(array $data): Medicine;

    /**
     * @param Medicine $medicine
     * @param array $data
     * @return Medicine
     */
    public function updateMedicine(Medicine $medicine, array $data): Medicine;

    /**
     * @param Medicine $medicine
     * @return void
     */
    public function deleteMedicine(Medicine $medicine): void;
}
