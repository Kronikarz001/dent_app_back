<?php

namespace App\Console\Commands;

use App\Exceptions\P1IntegrationNotImplementedException;
use App\Services\MedicineServiceInterface;
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
     * @param MedicineServiceInterface $medicineService
     */
    public function __construct(
        private readonly P1ServiceInterface $p1Service,
        private readonly MedicineServiceInterface $medicineService,
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

        $count = $this->medicineService->syncFromExternalSource($medicines);

        $this->info(sprintf('Zsynchronizowano %d leków z P1.', $count));

        return self::SUCCESS;
    }
}
