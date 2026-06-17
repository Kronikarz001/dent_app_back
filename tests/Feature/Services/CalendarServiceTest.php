<?php

namespace Tests\Feature\Services;

use App\Models\Calendar;
use App\Services\CalendarServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of CalendarServiceTest
 */
class CalendarServiceTest extends TestCase
{
    /**
     * @var CalendarServiceInterface|Application|mixed|object
     */
    private CalendarServiceInterface $service;

    protected const CALENDAR_TABLE = 'calendars';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CalendarServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetCalendarsReturnsPaginatedResults(): void
    {
        Calendar::factory()->count(3)->create();

        $result = $this->service->getCalendars();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetCalendarsListReturnsPaginator(): void
    {
        Calendar::factory()->count(2)->create();

        $result = $this->service->getCalendarsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateCalendarPersistsToDatabase(): void
    {
        $data = Calendar::factory()->make()->getAttributes();

        $calendar = $this->service->createCalendar($data);

        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertDatabaseHas(self::CALENDAR_TABLE, ['uuid' => $calendar->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateCalendarPersistsChangesToDatabase(): void
    {
        $calendar = Calendar::factory()->create();

        $result = $this->service->updateCalendar($calendar, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(Calendar::class, $result);
        $this->assertDatabaseHas(self::CALENDAR_TABLE, ['uuid' => $calendar->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteCalendarRemovesFromDatabase(): void
    {
        $calendar = Calendar::factory()->create();

        $this->service->deleteCalendar($calendar);

        $this->assertDatabaseMissing(self::CALENDAR_TABLE, ['uuid' => $calendar->uuid]);
    }
}
