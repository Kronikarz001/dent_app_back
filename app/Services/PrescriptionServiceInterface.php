<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of PrescriptionServiceInterface
 */
interface PrescriptionServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getPrescriptions(): LengthAwarePaginator;

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function getPrescriptionsForPatient(Patient $patient): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Prescription
     */
    public function createPrescription(array $data): Prescription;

    /**
     * @param Prescription $prescription
     * @param array $data
     * @return Prescription
     */
    public function updatePrescription(Prescription $prescription, array $data): Prescription;

    /**
     * @param Prescription $prescription
     * @return void
     */
    public function deletePrescription(Prescription $prescription): void;

    /**
     * @param Prescription $prescription
     * @return Prescription
     */
    public function issuePrescription(Prescription $prescription): Prescription;
}
