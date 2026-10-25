<?php

namespace App\Services;

use App\Enums\ReimbursementLevel;
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
        return $this->medicineRepository->findSelectAllWithPagination(['uuid', 'name']);
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

    /**
     * @param array<int, array<string, mixed>> $medicines
     * @return int
     */
    public function syncFromExternalSource(array $medicines): int
    {
        foreach ($medicines as $medicineData) {
            $reimbursementLevel = $medicineData['reimbursement_level'];

            $this->medicineRepository->updateOrCreateByEanGtin($medicineData['ean_gtin'], [
                'name' => $medicineData['name'],
                'description' => $medicineData['description'] ?? null,
                'reimbursement_level' => $reimbursementLevel instanceof ReimbursementLevel
                    ? $reimbursementLevel->value
                    : $reimbursementLevel,
                'external_reference' => $medicineData['external_reference'] ?? null,
            ]);
        }

        return count($medicines);
    }
}
