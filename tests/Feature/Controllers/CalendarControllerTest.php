<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\EmployeeSchedule;
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
    public function testIndexDoesNotIncludeEmployeeScheduleEntries(): void
    {
        $appointment = Calendar::factory()->create();
        $schedule = EmployeeSchedule::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index'));

        $response->assertOk();
        $uuids = collect($response->json('data'))->pluck('uuid')->all();
        $this->assertContains($appointment->uuid, $uuids);
        $this->assertNotContains($schedule->uuid, $uuids);
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
                'date' => '2026-12-31',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Test',
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithEmployeeTypeReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendar.store'), [
                'name' => 'Test',
                'type' => 'WORK',
                'date' => '2026-12-31',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testStoreWithNoShowSetsCalendarInactive(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendar.store'), [
                'name' => 'Test',
                'type' => 'EXAMINATION',
                'date' => '2026-12-31',
                'is_active' => true,
                'no_show' => true,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Test',
            'no_show' => true,
            'is_active' => false,
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
                'type' => 'OTHER',
                'date' => '2026-12-31',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Updated',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateCalendarWithNoShowSetsInactive(): void
    {
        $calendar = Calendar::factory()->create(['is_active' => true]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('calendar.update', ['calendar' => $calendar->uuid]), [
                'name' => $calendar->name,
                'type' => 'EXAMINATION',
                'date' => '2026-12-31',
                'is_active' => true,
                'no_show' => true,
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('calendars', [
            'uuid' => $calendar->uuid,
            'no_show' => true,
            'is_active' => false,
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
                'date' => '2026-12-31',
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
                'date' => '2026-12-31',
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
                'type' => 'OTHER',
                'date' => '2026-12-31',
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
    public function testAssignUsersRejectsOverlappingAppointmentForSameDentist(): void
    {
        $dentist = User::factory()->create();
        $existing = Calendar::factory()->create([
            'date' => '2030-01-10',
            'start_time' => '09:00',
            'end_time' => '09:30',
            'is_active' => true,
        ]);
        $existing->users()->attach($dentist->uuid);

        $newCalendar = Calendar::factory()->create([
            'date' => '2030-01-10',
            'start_time' => '09:15',
            'end_time' => '09:45',
            'is_active' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->patchJson(route('calendar.assignUsers', ['calendar' => $newCalendar->uuid]), [
                'users' => [$dentist->uuid],
            ]);

        $response->assertStatus(409);
        $this->assertDatabaseMissing('calendar_users', [
            'calendar_uuid' => $newCalendar->uuid,
            'userable_id' => $dentist->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersAllowsNonOverlappingAppointmentForSameDentist(): void
    {
        $dentist = User::factory()->create();
        $existing = Calendar::factory()->create([
            'date' => '2030-01-10',
            'start_time' => '09:00',
            'end_time' => '09:30',
            'is_active' => true,
        ]);
        $existing->users()->attach($dentist->uuid);

        $newCalendar = Calendar::factory()->create([
            'date' => '2030-01-10',
            'start_time' => '09:30',
            'end_time' => '10:00',
            'is_active' => true,
        ]);

        $this->callApiWithLoggedUser()
            ->patchJson(route('calendar.assignUsers', ['calendar' => $newCalendar->uuid]), [
                'users' => [$dentist->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $newCalendar->uuid,
            'userable_id' => $dentist->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testIndexIncludesUsersAndPatients(): void
    {
        $dentist = User::factory()->create(['first_name' => 'Anna', 'last_name' => 'Kowalska']);
        $patient = Patient::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);
        $calendar = Calendar::factory()->create();
        $calendar->users()->attach($dentist->uuid);
        $calendar->patients()->attach($patient->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.users.0.uuid', $dentist->uuid);
        $response->assertJsonPath('data.0.users.0.first_name', 'Anna');
        $response->assertJsonPath('data.0.patients.0.uuid', $patient->uuid);
        $response->assertJsonPath('data.0.patients.0.last_name', 'Kowalski');
        $this->assertArrayNotHasKey('email', $response->json('data.0.users.0'));
        $this->assertArrayNotHasKey('pesel', $response->json('data.0.patients.0'));
    }

    /**
     * @return void
     */
    public function testIndexFiltersByUserUuidsAndDateRange(): void
    {
        $dentist = User::factory()->create();
        $otherDentist = User::factory()->create();

        $matching = Calendar::factory()->create(['date' => '2026-11-13']);
        $matching->users()->attach($dentist->uuid);
        $otherDay = Calendar::factory()->create(['date' => '2026-11-14']);
        $otherDay->users()->attach($dentist->uuid);
        $otherDoctor = Calendar::factory()->create(['date' => '2026-11-13']);
        $otherDoctor->users()->attach($otherDentist->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index', [
                'calendar' => [
                    'user_uuids' => [$dentist->uuid],
                    'date_from' => '2026-11-13',
                    'date_to' => '2026-11-13',
                ],
            ]));

        $response->assertOk();
        $this->assertSame([$matching->uuid], collect($response->json('data'))->pluck('uuid')->all());
    }

    /**
     * @return void
     */
    public function testIndexRejectsInvalidUserUuid(): void
    {
        $this->callApiWithLoggedUser()
            ->getJson(route('calendar.index', ['calendar' => ['user_uuids' => ['not-a-uuid']]]))
            ->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testTodayReturnsOnlyTodayAppointmentsSortedByStartTime(): void
    {
        $later = Calendar::factory()->create(['date' => now()->toDateString(), 'start_time' => '12:00', 'end_time' => '13:00']);
        $earlier = Calendar::factory()->create(['date' => now()->toDateString(), 'start_time' => '08:00', 'end_time' => '09:00']);
        Calendar::factory()->create(['date' => now()->addDay()->toDateString()]);
        EmployeeSchedule::factory()->create(['date' => now()->toDateString()]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.today'));

        $response->assertOk();
        $this->assertTrue(array_is_list($response->json()));
        $this->assertSame([$earlier->uuid, $later->uuid], collect($response->json())->pluck('uuid')->all());
        $this->assertArrayHasKey('users', $response->json('0'));
        $this->assertArrayHasKey('patients', $response->json('0'));
    }

    /**
     * @return void
     */
    public function testShowIncludesUsersPatientsAndDentalExaminations(): void
    {
        $dentist = User::factory()->create();
        $patient = Patient::factory()->create();
        $examination = DentalExamination::factory()->create();
        $calendar = Calendar::factory()->create();
        $calendar->users()->attach($dentist->uuid);
        $calendar->patients()->attach($patient->uuid);
        $calendar->dentalExaminations()->attach($examination->uuid);

        $this->callApiWithLoggedUser()
            ->getJson(route('calendar.show', ['calendar' => $calendar->uuid]))
            ->assertOk()
            ->assertJsonPath('users.0.uuid', $dentist->uuid)
            ->assertJsonPath('patients.0.uuid', $patient->uuid)
            ->assertJsonPath('dental_examinations.0.uuid', $examination->uuid);
    }
}
