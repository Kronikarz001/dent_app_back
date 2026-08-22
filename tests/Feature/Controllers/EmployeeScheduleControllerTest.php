<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of EmployeeScheduleControllerTest
 */
class EmployeeScheduleControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        EmployeeSchedule::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('employee-schedule.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexDoesNotIncludeAppointmentCalendarEntries(): void
    {
        $appointment = Calendar::factory()->create();
        $schedule = EmployeeSchedule::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('employee-schedule.index'));

        $response->assertOk();
        $uuids = collect($response->json('data'))->pluck('uuid')->all();
        $this->assertContains($schedule->uuid, $uuids);
        $this->assertNotContains($appointment->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testShowScheduleReturnSuccessResponse(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('employee-schedule.show', ['employeeSchedule' => $schedule->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowDoesNotResolveAppointmentCalendarEntry(): void
    {
        $appointment = Calendar::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('employee-schedule.show', ['employeeSchedule' => $appointment->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStoreScheduleReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('employee-schedule.store'), [
                'name' => 'Test',
                'type' => 'WORK',
                'date' => '2026-12-31',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('calendars', [
            'name' => 'Test',
            'type' => 'WORK',
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithAppointmentTypeReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('employee-schedule.store'), [
                'name' => 'Test',
                'type' => 'EXAMINATION',
                'date' => '2026-12-31',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateScheduleReturnNoContentResponse(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('employee-schedule.update', ['employeeSchedule' => $schedule->uuid]), [
                'name' => 'Updated',
                'type' => 'VACATION',
                'date' => '2026-12-31',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('calendars', [
            'uuid' => $schedule->uuid,
            'name' => 'Updated',
            'type' => 'VACATION',
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteScheduleReturnNoContentResponse(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('employee-schedule.destroy', ['employeeSchedule' => $schedule->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($schedule);
    }

    /**
     * @return void
     */
    public function testAssignUsersReturnNoContentResponse(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $user = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('employee-schedule.assignUsers', ['employeeSchedule' => $schedule->uuid]), [
                'users' => [$user->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $schedule->uuid,
            'userable_id' => $user->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersWithNonExistentUuidReturnsValidationError(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->patchJson(route('employee-schedule.assignUsers', ['employeeSchedule' => $schedule->uuid]), [
                'users' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }
}
