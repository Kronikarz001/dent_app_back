<?php

namespace Tests\Feature\Controllers;

use App\Models\DentalExamination;
use App\Models\Material;
use Tests\TestCase;

/**
 * Summary of MaterialControllerTest
 */
class MaterialControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexMaterialsReturnSuccessResponse(): void
    {
        Material::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexMaterialsListReturnSuccessResponse(): void
    {
        Material::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexFiltersByExactFieldValue(): void
    {
        Material::factory()->create(['name' => 'Zbigniew']);
        $target = Material::factory()->create(['name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.index', ['material' => ['name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        Material::factory()->create(['name' => 'Bartosz']);
        Material::factory()->create(['name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.index', ['sort' => 'name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = Material::factory()->create(['name' => 'Wyjatkowy']);
        Material::factory()->create(['name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testShowMaterialReturnSuccessResponse(): void
    {
        $material = Material::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.show', ['material' => $material->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowMissingMaterialReturnsNotFound(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.show', ['material' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStoreMaterialReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('material.store'), [
                'name' => 'Kompozyt światłoutwardzalny',
                'description' => 'Materiał kompozytowy',
                'short_description' => 'Kompozyt',
                'price' => 50,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('materials', [
            'name' => 'Kompozyt światłoutwardzalny',
            'price' => 50,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutNameReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('material.store'), [
                'description' => 'Brak nazwy',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateMaterialReturnNoContentResponse(): void
    {
        $material = Material::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('material.update', ['material' => $material->uuid]), [
                'name' => 'Updated',
                'price' => 200,
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('materials', [
            'uuid' => $material->uuid,
            'name' => 'Updated',
            'price' => 200,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithDentalExaminationsAttachesThem(): void
    {
        $dentalExamination = DentalExamination::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('material.store'), [
                'name' => 'Kompozyt światłoutwardzalny',
                'dental_examinations' => [$dentalExamination->uuid],
            ]);

        $response->assertCreated();

        $material = Material::query()->where('name', 'Kompozyt światłoutwardzalny')->first();
        $this->assertDatabaseHas('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $dentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithNonExistentDentalExaminationReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('material.store'), [
                'name' => 'Kompozyt światłoutwardzalny',
                'dental_examinations' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateWithDentalExaminationsReplacesPreviousAssignments(): void
    {
        $material = Material::factory()->create();
        $oldDentalExamination = DentalExamination::factory()->create();
        $newDentalExamination = DentalExamination::factory()->create();
        $material->dentalExaminations()->sync([$oldDentalExamination->uuid]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('material.update', ['material' => $material->uuid]), [
                'name' => $material->name,
                'dental_examinations' => [$newDentalExamination->uuid],
            ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $oldDentalExamination->uuid,
        ]);
        $this->assertDatabaseHas('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $newDentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteMaterialReturnNoContentResponse(): void
    {
        $material = Material::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('material.destroy', ['material' => $material->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('materials', ['uuid' => $material->uuid]);
    }

    /**
     * @return void
     */
    public function testExportMaterialReturnSuccessResponse(): void
    {
        Material::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('material.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
