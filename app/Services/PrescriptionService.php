<?php

namespace App\Services;

use App\Enums\PrescriptionStatus;
use App\Models\Patient;
use App\Models\Prescription;
use App\Repositories\PrescriptionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PrescriptionService
 */
readonly class PrescriptionService implements PrescriptionServiceInterface
{
    /**
     * @param PrescriptionRepositoryInterface $prescriptionRepository
     * @param AuditServiceInterface $auditService
     * @param P1ServiceInterface $p1Service
     */
    public function __construct(
        private PrescriptionRepositoryInterface $prescriptionRepository,
        private AuditServiceInterface $auditService,
        private P1ServiceInterface $p1Service,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getPrescriptions(): LengthAwarePaginator
    {
        return $this->prescriptionRepository->findAllWithPagination();
    }

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function getPrescriptionsForPatient(Patient $patient): LengthAwarePaginator
    {
        return $this->prescriptionRepository->findAllByPatient($patient->uuid);
    }

    /**
     * @param array $data
     * @return Prescription
     */
    public function createPrescription(array $data): Prescription
    {
        $medicines = $data['medicines'] ?? null;
        unset($data['medicines']);

        $prescription = $this->prescriptionRepository->create($data);

        if ($medicines !== null) {
            $this->syncMedicines($prescription, $medicines);
        }

        return $prescription;
    }

    /**
     * @param Prescription $prescription
     * @param array $data
     * @return Prescription
     */
    public function updatePrescription(Prescription $prescription, array $data): Prescription
    {
        $medicines = $data['medicines'] ?? null;
        unset($data['medicines']);

        $prescription = $this->prescriptionRepository->update($prescription, $data);

        if ($medicines !== null) {
            $this->syncMedicines($prescription, $medicines);
        }

        return $prescription;
    }

    /**
     * @param Prescription $prescription
     * @return void
     */
    public function deletePrescription(Prescription $prescription): void
    {
        $this->prescriptionRepository->delete($prescription);
    }

    /**
     * @param Prescription $prescription
     * @return Prescription
     */
    public function issuePrescription(Prescription $prescription): Prescription
    {
        $result = $this->p1Service->issuePrescription($prescription);

        return $this->prescriptionRepository->update($prescription, [
            'status' => PrescriptionStatus::ISSUED->value,
            'code' => $result['code'],
            'external_reference' => $result['external_reference'] ?? $prescription->external_reference,
        ]);
    }

    /**
     * @param Prescription $prescription
     * @param array $medicines
     * @return void
     */
    private function syncMedicines(Prescription $prescription, array $medicines): void
    {
        $syncPayload = collect($medicines)->mapWithKeys(fn (array $item) => [
            $item['medicine_uuid'] => [
                'quantity' => $item['quantity'],
                'dosage' => $item['dosage'],
            ],
        ])->all();

        $this->auditService->recordSync($prescription, 'medicines', $prescription->medicines()->sync($syncPayload));
    }
}
