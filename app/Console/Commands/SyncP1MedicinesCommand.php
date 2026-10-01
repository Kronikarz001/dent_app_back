<?php

namespace App\Console\Commands;

use App\Enums\ReimbursementLevel;
use App\Exceptions\P1IntegrationNotImplementedException;
use App\Models\Medicine;
use App\Services\P1ServiceInterface;
use Illuminate\Console\Command;

/**
 * Summary of SyncP1MedicinesCommand
 */
class SyncP1MedicinesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'p1:sync-medicines';

    /**
     * @var string
     */
    protected $description = 'Synchronizuje lokalną bazę leków z listą leków z P1 (eZdrowie)';

    /**
     * @param P1ServiceInterface $p1Service
     */
    public function __construct(
        private readonly P1ServiceInterface $p1Service,
    ) {
        parent::__construct();
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            $medicines = $this->p1Service->fetchMedicines();
        } catch (P1IntegrationNotImplementedException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($medicines as $medicineData) {
            $reimbursementLevel = $medicineData['reimbursement_level'];

            Medicine::query()->updateOrCreate(
                ['ean_gtin' => $medicineData['ean_gtin']],
                [
                    'name' => $medicineData['name'],
                    'description' => $medicineData['description'] ?? null,
                    'reimbursement_level' => $reimbursementLevel instanceof ReimbursementLevel
                        ? $reimbursementLevel->value
                        : $reimbursementLevel,
                    'external_reference' => $medicineData['external_reference'] ?? null,
                ]
            );
        }

        $this->info(sprintf('Zsynchronizowano %d leków z P1.', count($medicines)));

        return self::SUCCESS;
    }
}
