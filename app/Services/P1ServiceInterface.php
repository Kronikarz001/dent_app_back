<?php

namespace App\Services;

use App\Models\Prescription;

/**
 * Summary of P1ServiceInterface
 *
 * Granica integracji z platformą P1 (eZdrowie/CeZ). Prawdziwa komunikacja z P1
 * odbywa się przez dokumenty HL7 CDA po TLS z certyfikatem klienckim (nie przez
 * prosty REST+token) - szczegóły w "Podręczniku implementatora PIK" udostępnianym
 * przez CeZ po zatwierdzeniu wniosku o dostęp do środowiska testowego.
 * Implementacja tego interfejsu odpowiada za przetłumaczenie tamtego formatu na
 * poniższe, znormalizowane struktury używane wewnątrz aplikacji.
 */
interface P1ServiceInterface
{
    /**
     * Zwraca listę leków znormalizowaną do pól modelu Medicine, np.:
     * [['name' => ..., 'description' => ..., 'ean_gtin' => ..., 'reimbursement_level' => ..., 'external_reference' => ...], ...]
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchMedicines(): array;

    /**
     * Wystawia receptę w P1 i zwraca dane przypisane przez ten system.
     *
     * @param Prescription $prescription
     * @return array{code: string, external_reference: string|null}
     */
    public function issuePrescription(Prescription $prescription): array;
}
