<?php

namespace Tests\Unit\Repositories;

use App\Models\Calendar;
use App\Repositories\CalendarRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of CalendarRepositoryTest
 */
class CalendarRepositoryTest extends TestCase
{
    /**
     * @var CalendarRepositoryInterface
     */
    private CalendarRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CalendarRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsCalendar(): void
    {
        $calendar = $this->repository->create(Calendar::factory()->make()->toArray());

        $this->assertDatabaseHas('calendars', ['uuid' => $calendar->uuid]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingCalendar(): void
    {
        $calendar = Calendar::factory()->create();

        $this->assertTrue($this->repository->findByUuid($calendar->uuid)->is($calendar));
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repository->findByUuid((string) Str::uuid()));
    }

    /**
     * @return void
     */
    public function testFindAllByUuidsReturnsOnlyMatchingCalendars(): void
    {
        $first = Calendar::factory()->create();
        $second = Calendar::factory()->create();
        Calendar::factory()->create();

        $result = $this->repository->findAllByUuids([$first->uuid, $second->uuid]);

        $this->assertCount(2, $result);
    }

    /**
     * @return void
     */
    public function testUpdateChangesCalendarAttributes(): void
    {
        $calendar = Calendar::factory()->create();

        $updated = $this->repository->update($calendar, ['name' => 'Zmienione']);

        $this->assertSame('Zmienione', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesCalendar(): void
    {
        $calendar = Calendar::factory()->create();

        $this->assertTrue($this->repository->delete($calendar));
        $this->assertDatabaseMissing('calendars', ['uuid' => $calendar->uuid]);
    }
}
