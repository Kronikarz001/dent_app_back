<?php

namespace Tests\Feature\Controllers;

use App\Enums\ReimbursementLevel;
use App\Models\Medicine;
use Tests\TestCase;

/**
 * Summary of MedicineControllerTest
 */
class MedicineControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexMedicinesReturnSuccessResponse(): void
    {
        Medicine::factory()->count(5)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('medicine.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexMedicinesListReturnSuccessResponse(): void
    {
        Medicine::factory()->count(5)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('medicine.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowMedicineReturnSuccessResponse(): void
    {
        $medicine = Medicine::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('medicine.show', ['medicine' => $medicine->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $medicine->uuid);
    }

    /**
     * @return void
     */
    public function testShowMissingMedicineReturnsNotFound(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('medicine.show', ['medicine' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStoreMedicineReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('medicine.store'), [
                'name' => 'Apap',
                'description' => 'Lek przeciwbólowy',
                'ean_gtin' => '5900512345678',
                'reimbursement_level' => ReimbursementLevel::FULL->value,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('medicines', [
            'name' => 'Apap',
            'ean_gtin' => '5900512345678',
            'reimbursement_level' => ReimbursementLevel::FULL->value,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutNameReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('medicine.store'), [
                'reimbursement_level' => ReimbursementLevel::FULL->value,
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testStoreWithDuplicateEanGtinReturnsValidationError(): void
    {
        Medicine::factory()->create(['ean_gtin' => '5900512345678']);

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('medicine.store'), [
                'name' => 'Inny lek',
                'ean_gtin' => '5900512345678',
                'reimbursement_level' => ReimbursementLevel::FULL->value,
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateMedicineReturnNoContentResponse(): void
    {
        $medicine = Medicine::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('medicine.update', ['medicine' => $medicine->uuid]), [
                'name' => 'Zaktualizowany',
                'reimbursement_level' => ReimbursementLevel::FREE->value,
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('medicines', [
            'uuid' => $medicine->uuid,
            'name' => 'Zaktualizowany',
            'reimbursement_level' => ReimbursementLevel::FREE->value,
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteMedicineReturnNoContentResponse(): void
    {
        $medicine = Medicine::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('medicine.destroy', ['medicine' => $medicine->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('medicines', ['uuid' => $medicine->uuid]);
    }
}
