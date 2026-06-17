<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
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
    public function testExportCalendarReturnSuccessResponse(): void
    {
        Calendar::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendar.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
