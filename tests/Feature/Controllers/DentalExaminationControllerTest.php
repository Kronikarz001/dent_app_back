<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\Material;
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
    public function testStoreWithMaterialsAttachesThem(): void
    {
        $material = Material::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'name' => 'Przegląd jamy ustnej',
                'materials' => [$material->uuid],
            ]);

        $response->assertCreated();

        $dentalExamination = DentalExamination::query()->where('name', 'Przegląd jamy ustnej')->first();
        $this->assertDatabaseHas('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $material->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithNonExistentMaterialReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'name' => 'Przegląd jamy ustnej',
                'materials' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateWithMaterialsReplacesPreviousAssignments(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $oldMaterial = Material::factory()->create();
        $newMaterial = Material::factory()->create();
        $dentalExamination->materials()->sync([$oldMaterial->uuid]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('dentalExamination.update', ['dental_examination' => $dentalExamination->uuid]), [
                'name' => $dentalExamination->name,
                'materials' => [$newMaterial->uuid],
            ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $oldMaterial->uuid,
        ]);
        $this->assertDatabaseHas('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $newMaterial->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithCalendarsAttachesThem(): void
    {
        $calendar = Calendar::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'name' => 'Przegląd jamy ustnej',
                'calendars' => [$calendar->uuid],
            ]);

        $response->assertCreated();

        $dentalExamination = DentalExamination::query()->where('name', 'Przegląd jamy ustnej')->first();
        $this->assertDatabaseHas('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $calendar->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithNonExistentCalendarReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalExamination.store'), [
                'name' => 'Przegląd jamy ustnej',
                'calendars' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateWithCalendarsReplacesPreviousAssignments(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $oldCalendar = Calendar::factory()->create();
        $newCalendar = Calendar::factory()->create();
        $dentalExamination->calendars()->sync([$oldCalendar->uuid]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('dentalExamination.update', ['dental_examination' => $dentalExamination->uuid]), [
                'name' => $dentalExamination->name,
                'calendars' => [$newCalendar->uuid],
            ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $oldCalendar->uuid,
        ]);
        $this->assertDatabaseHas('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $newCalendar->uuid,
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
}
