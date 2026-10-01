<?php

namespace App\Services;

use App\Exceptions\P1IntegrationNotImplementedException;
use App\Models\Prescription;

/**
 * Summary of P1Service
 */
readonly class P1Service implements P1ServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws P1IntegrationNotImplementedException
     */
    public function fetchMedicines(): array
    {
        throw new P1IntegrationNotImplementedException(
            'Pobieranie listy leków z P1 wymaga dostępu do środowiska testowego CeZ (certyfikat TLS, dokumentacja HL7 CDA) - integracja jeszcze nie jest podłączona.'
        );
    }

    /**
     * @param Prescription $prescription
     * @return array{code: string, external_reference: string|null}
     *
     * @throws P1IntegrationNotImplementedException
     */
    public function issuePrescription(Prescription $prescription): array
    {
        throw new P1IntegrationNotImplementedException(
            'Wystawianie recepty w P1 wymaga dostępu do środowiska testowego CeZ (certyfikat TLS, dokumentacja HL7 CDA) - integracja jeszcze nie jest podłączona.'
        );
    }
}
