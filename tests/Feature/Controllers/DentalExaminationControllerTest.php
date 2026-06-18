<?php

namespace Tests\Feature\Controllers;

use App\Models\DentalExamination;
use Tests\TestCase;

/**
 * Summary of DentalExaminationControllerTest
 */
class DentalExaminationControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexDentalExaminationsReturnSuccessResponse(): void
    {
        DentalExamination::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexDentalExaminationsListReturnSuccessResponse(): void
    {
        DentalExamination::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexFiltersByExactFieldValue(): void
    {
        DentalExamination::factory()->create(['name' => 'Zbigniew']);
        $target = DentalExamination::factory()->create(['name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.index', ['dentalExamination' => ['name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        DentalExamination::factory()->create(['name' => 'Bartosz']);
        DentalExamination::factory()->create(['name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.index', ['sort' => 'name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = DentalExamination::factory()->create(['name' => 'Wyjatkowy']);
        DentalExamination::factory()->create(['name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testShowDentalExaminationReturnSuccessResponse(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.show', ['dental_examination' => $dentalExamination->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowMissingDentalExaminationReturnsNotFound(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.show', ['dental_examination' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStoreDentalExaminationReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'name' => 'Przegląd jamy ustnej',
                'description' => 'Pełny przegląd',
                'short_description' => 'Przegląd',
                'price' => 150,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('dental_examinations', [
            'name' => 'Przegląd jamy ustnej',
            'price' => 150,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutNameReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'description' => 'Brak nazwy',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationReturnNoContentResponse(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('dentalExamination.update', ['dental_examination' => $dentalExamination->uuid]), [
                'name' => 'Updated',
                'price' => 200,
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('dental_examinations', [
            'uuid' => $dentalExamination->uuid,
            'name' => 'Updated',
            'price' => 200,
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteDentalExaminationReturnNoContentResponse(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('dentalExamination.destroy', ['dental_examination' => $dentalExamination->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('dental_examinations', ['uuid' => $dentalExamination->uuid]);
    }

    /**
     * @return void
     */
    public function testExportDentalExaminationReturnSuccessResponse(): void
    {
        DentalExamination::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalExamination.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
