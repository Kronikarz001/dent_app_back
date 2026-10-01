<?php

namespace App\Services;

use App\Models\Medicine;
use App\Repositories\MedicineRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of MedicineService
 */
readonly class MedicineService implements MedicineServiceInterface
{
    /**
     * @param MedicineRepositoryInterface $medicineRepository
     */
    public function __construct(
        private MedicineRepositoryInterface $medicineRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getMedicines(): LengthAwarePaginator
    {
        return $this->medicineRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getMedicinesList(): LengthAwarePaginator
    {
        return $this->medicineRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Medicine
     */
    public function createMedicine(array $data): Medicine
    {
        return $this->medicineRepository->create($data);
    }

    /**
     * @param Medicine $medicine
     * @param array $data
     * @return Medicine
     */
    public function updateMedicine(Medicine $medicine, array $data): Medicine
    {
        return $this->medicineRepository->update($medicine, $data);
    }

    /**
     * @param Medicine $medicine
     * @return void
     */
    public function deleteMedicine(Medicine $medicine): void
    {
        $this->medicineRepository->delete($medicine);
    }
}
