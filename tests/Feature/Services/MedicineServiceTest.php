<?php

namespace Tests\Feature\Services;

use App\Enums\ReimbursementLevel;
use App\Models\Medicine;
use App\Services\MedicineServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of MedicineServiceTest
 */
class MedicineServiceTest extends TestCase
{
    /**
     * @var MedicineServiceInterface
     */
    private MedicineServiceInterface $service;

    protected const MEDICINES_TABLE = 'medicines';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MedicineServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetMedicinesReturnsPaginatedResults(): void
    {
        Medicine::factory()->count(3)->create();

        $result = $this->service->getMedicines();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testCreateMedicinePersistsToDatabase(): void
    {
        $data = Medicine::factory()->make()->toArray();

        $medicine = $this->service->createMedicine($data);

        $this->assertDatabaseHas(self::MEDICINES_TABLE, ['uuid' => $medicine->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateMedicinePersistsChanges(): void
    {
        $medicine = Medicine::factory()->create();

        $this->service->updateMedicine($medicine, ['name' => 'Nowa nazwa']);

        $this->assertDatabaseHas(self::MEDICINES_TABLE, ['uuid' => $medicine->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteMedicineRemovesFromDatabase(): void
    {
        $medicine = Medicine::factory()->create();

        $this->service->deleteMedicine($medicine);

        $this->assertDatabaseMissing(self::MEDICINES_TABLE, ['uuid' => $medicine->uuid]);
    }

    /**
     * @return void
     */
    public function testSyncFromExternalSourceCreatesNewMedicines(): void
    {
        $count = $this->service->syncFromExternalSource([
            [
                'name' => 'Apap',
                'description' => 'Lek przeciwbólowy',
                'ean_gtin' => '5900512345678',
                'reimbursement_level' => ReimbursementLevel::FULL,
                'external_reference' => 'P1-123',
            ],
        ]);

        $this->assertSame(1, $count);
        $this->assertDatabaseHas(self::MEDICINES_TABLE, [
            'ean_gtin' => '5900512345678',
            'name' => 'Apap',
            'reimbursement_level' => ReimbursementLevel::FULL->value,
            'external_reference' => 'P1-123',
        ]);
    }

    /**
     * @return void
     */
    public function testSyncFromExternalSourceUpdatesExistingMedicineByEanGtin(): void
    {
        Medicine::factory()->create(['ean_gtin' => '5900512345678', 'name' => 'Stara nazwa']);

        $this->service->syncFromExternalSource([
            [
                'name' => 'Nowa nazwa',
                'ean_gtin' => '5900512345678',
                'reimbursement_level' => ReimbursementLevel::FREE,
            ],
        ]);

        $this->assertDatabaseCount(self::MEDICINES_TABLE, 1);
        $this->assertDatabaseHas(self::MEDICINES_TABLE, [
            'ean_gtin' => '5900512345678',
            'name' => 'Nowa nazwa',
            'reimbursement_level' => ReimbursementLevel::FREE->value,
        ]);
    }

    /**
     * @return void
     */
    public function testSyncFromExternalSourceAcceptsReimbursementLevelAsPlainString(): void
    {
        $this->service->syncFromExternalSource([
            [
                'name' => 'Apap',
                'ean_gtin' => '5900512345679',
                'reimbursement_level' => 'FULL',
            ],
        ]);

        $this->assertDatabaseHas(self::MEDICINES_TABLE, [
            'ean_gtin' => '5900512345679',
            'reimbursement_level' => 'FULL',
        ]);
    }
}
