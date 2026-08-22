<?php

namespace Tests\Feature\Services;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Services\EmployeeScheduleServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Summary of EmployeeScheduleServiceTest
 */
class EmployeeScheduleServiceTest extends TestCase
{
    /**
     * @var EmployeeScheduleServiceInterface|Application|mixed|object
     */
    private EmployeeScheduleServiceInterface $service;

    protected const CALENDAR_TABLE = 'calendars';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EmployeeScheduleServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetSchedulesReturnsPaginatedResults(): void
    {
        EmployeeSchedule::factory()->count(3)->create();

        $result = $this->service->getSchedules();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetSchedulesListReturnsPaginator(): void
    {
        EmployeeSchedule::factory()->count(2)->create();

        $result = $this->service->getSchedulesList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateSchedulePersistsToDatabase(): void
    {
        $data = EmployeeSchedule::factory()->make()->getAttributes();

        $schedule = $this->service->createSchedule($data);

        $this->assertInstanceOf(EmployeeSchedule::class, $schedule);
        $this->assertDatabaseHas(self::CALENDAR_TABLE, ['uuid' => $schedule->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateSchedulePersistsChangesToDatabase(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $result = $this->service->updateSchedule($schedule, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(EmployeeSchedule::class, $result);
        $this->assertDatabaseHas(self::CALENDAR_TABLE, ['uuid' => $schedule->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteScheduleRemovesFromDatabase(): void
    {
        $schedule = EmployeeSchedule::factory()->create();

        $this->service->deleteSchedule($schedule);

        $this->assertDatabaseMissing(self::CALENDAR_TABLE, ['uuid' => $schedule->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignUsersAttachesUsers(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $user = User::factory()->create();

        $this->service->assignUsers($schedule, ['users' => [$user->uuid]]);

        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $schedule->uuid,
            'userable_id' => $user->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersReplacesPreviousAssignments(): void
    {
        $schedule = EmployeeSchedule::factory()->create();
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();

        $this->service->assignUsers($schedule, ['users' => [$oldUser->uuid]]);
        $this->service->assignUsers($schedule, ['users' => [$newUser->uuid]]);

        $this->assertDatabaseMissing('calendar_users', [
            'calendar_uuid' => $schedule->uuid,
            'userable_id' => $oldUser->uuid,
        ]);
        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $schedule->uuid,
            'userable_id' => $newUser->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersRecordsAuditEntryWhenAuthenticated(): void
    {
        $actor = User::factory()->create();
        Auth::setUser($actor);

        $schedule = EmployeeSchedule::factory()->create();
        $user = User::factory()->create();

        $this->service->assignUsers($schedule, ['users' => [$user->uuid]]);

        $audit = Audit::query()
            ->where('auditable_id', $schedule->uuid)
            ->where('type', AuditableEventType::UPDATE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($actor->uuid, $audit->user_uuid);
        $this->assertSame(['users' => [$user->uuid]], $audit->change_to);
    }
}
