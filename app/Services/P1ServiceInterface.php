<?php

namespace App\Services;

use App\Models\Prescription;

/**
 * Summary of P1ServiceInterface
 */
interface P1ServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchMedicines(): array;

    /**
     * @param Prescription $prescription
     * @return array{code: string, external_reference: string|null}
     */
    public function issuePrescription(Prescription $prescription): array;
}
