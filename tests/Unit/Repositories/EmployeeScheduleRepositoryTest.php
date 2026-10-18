<?php

namespace Tests\Unit\Repositories;

use App\Models\EmployeeSchedule;
use App\Repositories\EmployeeScheduleRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of EmployeeScheduleRepositoryTest
 */
class EmployeeScheduleRepositoryTest extends TestCase
{
    /**
     * @var EmployeeScheduleRepositoryInterface
     */
    private EmployeeScheduleRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(EmployeeScheduleRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsEmployeeSchedule(): void
    {
        $schedule = $this->repository->create(EmployeeSchedule::factory()->make()->toArray());

        $this->assertDatabaseHas('calendars', ['uuid' => $schedule->uuid]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingSchedule(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $this->assertTrue($this->repository->findByUuid($schedule->uuid)->is($schedule));
    }

    /**
     * @return void
     */
    public function testFindAllByUuidsReturnsOnlyMatchingSchedules(): void
    {
        $first = EmployeeSchedule::factory()->create();
        $second = EmployeeSchedule::factory()->create();
        EmployeeSchedule::factory()->create();

        $result = $this->repository->findAllByUuids([$first->uuid, $second->uuid]);

        $this->assertCount(2, $result);
    }

    /**
     * @return void
     */
    public function testUpdateChangesScheduleAttributes(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $updated = $this->repository->update($schedule, ['name' => 'Zmienione']);

        $this->assertSame('Zmienione', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesSchedule(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $this->assertTrue($this->repository->delete($schedule));
        $this->assertDatabaseMissing('calendars', ['uuid' => $schedule->uuid]);
    }
}
