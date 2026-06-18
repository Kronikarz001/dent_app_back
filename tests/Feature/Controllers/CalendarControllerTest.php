<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of CalendarControllerTest
 */
class CalendarControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexUsersReturnSuccessResponse(): void
    {
        Calendar::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexUsersListReturnSuccessResponse(): void
    {
        Calendar::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexFiltersByExactFieldValue(): void
    {
        Calendar::factory()->create(['name' => 'Zbigniew']);
        $target = Calendar::factory()->create(['name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index', ['calendar' => ['name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        Calendar::factory()->create(['name' => 'Bartosz']);
        Calendar::factory()->create(['name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index', ['sort' => 'name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = Calendar::factory()->create(['name' => 'Wyjatkowy']);
        Calendar::factory()->create(['name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testShowCalendarReturnSuccessResponse(): void
    {
        $calendar = Calendar::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.show', ['calendar' => $calendar->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreCalendarReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendar.store'), [
                'name' => 'Test',
                'type' => 'EXAMINATION',
                'end_date' => '2026-12-31 00:00:00',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Test',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateCalendarReturnNoContentResponse(): void
    {
        $calendar = Calendar::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('calendar.update', ['calendar' => $calendar->uuid]), [
                'name' => 'Updated',
                'type' => 'WORK',
                'end_date' => '2026-12-31 00:00:00',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Updated',
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithDentalExaminationsAttachesThem(): void
    {
        $dentalExamination = DentalExamination::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendar.store'), [
                'name' => 'Test',
                'type' => 'EXAMINATION',
                'end_date' => '2026-12-31 00:00:00',
                'dental_examinations' => [$dentalExamination->uuid],
            ]);

        $response->assertCreated();

        $calendar = Calendar::query()->where('name', 'Test')->first();
        $this->assertDatabaseHas('calendars_dental_examinations', [
            'calendar_uuid' => $calendar->uuid,
            'dental_examination_uuid' => $dentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithNonExistentDentalExaminationReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendar.store'), [
                'name' => 'Test',
                'type' => 'EXAMINATION',
                'end_date' => '2026-12-31 00:00:00',
                'dental_examinations' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateWithDentalExaminationsReplacesPreviousAssignments(): void
    {
        $calendar = Calendar::factory()->create();
        $oldDentalExamination = DentalExamination::factory()->create();
        $newDentalExamination = DentalExamination::factory()->create();
        $calendar->dentalExaminations()->sync([$oldDentalExamination->uuid]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('calendar.update', ['calendar' => $calendar->uuid]), [
                'name' => $calendar->name,
                'type' => 'WORK',
                'end_date' => '2026-12-31 00:00:00',
                'dental_examinations' => [$newDentalExamination->uuid],
            ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('calendars_dental_examinations', [
            'calendar_uuid' => $calendar->uuid,
            'dental_examination_uuid' => $oldDentalExamination->uuid,
        ]);
        $this->assertDatabaseHas('calendars_dental_examinations', [
            'calendar_uuid' => $calendar->uuid,
            'dental_examination_uuid' => $newDentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteCalendarReturnNoContentResponse(): void
    {
        $calendar = Calendar::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('calendar.destroy', ['calendar' => $calendar->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($calendar);
    }

    /**
     * @return void
     */
    public function testAssignUsersReturnNoContentResponse(): void
    {
        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('calendar.assignUsers', ['calendar' => $calendar->uuid]), [
                'users' => [$user->uuid],
                'patients' => [$patient->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $calendar->uuid,
            'userable_id' => $user->uuid,
            'userable_type' => User::class,
        ]);
        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $calendar->uuid,
            'userable_id' => $patient->uuid,
            'userable_type' => Patient::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersWithNonExistentUuidReturnsValidationError(): void
    {
        $calendar = Calendar::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->patchJson(route('calendar.assignUsers', ['calendar' => $calendar->uuid]), [
                'users' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testExportCalendarReturnSuccessResponse(): void
    {
        Calendar::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
