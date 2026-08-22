<?php

namespace Tests\Feature\Services;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\Patient;
use App\Models\User;
use App\Services\CalendarServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
    public function testCreateCalendarWithNoShowForcesInactive(): void
    {
        $data = array_merge(Calendar::factory()->make()->getAttributes(), [
            'is_active' => true,
            'no_show' => true,
        ]);

        $calendar = $this->service->createCalendar($data);

        $this->assertTrue($calendar->no_show);
        $this->assertFalse($calendar->is_active);
    }

    /**
     * @return void
     */
    public function testUpdateCalendarWithNoShowForcesInactive(): void
    {
        $calendar = Calendar::factory()->create(['is_active' => true]);

        $result = $this->service->updateCalendar($calendar, ['is_active' => true, 'no_show' => true]);

        $this->assertTrue($result->no_show);
        $this->assertFalse($result->is_active);
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

    /**
     * @return void
     */
    public function testAssignUsersAttachesUsersAndPatients(): void
    {
        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->service->assignUsers($calendar, [
            'users' => [$user->uuid],
            'patients' => [$patient->uuid],
        ]);

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
    public function testAssignUsersReplacesPreviousAssignments(): void
    {
        $calendar = Calendar::factory()->create();
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();

        $this->service->assignUsers($calendar, ['users' => [$oldUser->uuid]]);
        $this->service->assignUsers($calendar, ['users' => [$newUser->uuid]]);

        $this->assertDatabaseMissing('calendar_users', [
            'calendar_uuid' => $calendar->uuid,
            'userable_id' => $oldUser->uuid,
        ]);
        $this->assertDatabaseHas('calendar_users', [
            'calendar_uuid' => $calendar->uuid,
            'userable_id' => $newUser->uuid,
            'userable_type' => User::class,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersWithEmptyDataDetachesEveryone(): void
    {
        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();
        $this->service->assignUsers($calendar, ['users' => [$user->uuid]]);

        $this->service->assignUsers($calendar, []);

        $this->assertDatabaseMissing('calendar_users', ['calendar_uuid' => $calendar->uuid]);
    }

    /**
     * @return void
     */
    public function testCreateCalendarSyncsDentalExaminations(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $data = array_merge(Calendar::factory()->make()->getAttributes(), [
            'dental_examinations' => [$dentalExamination->uuid],
        ]);

        $calendar = $this->service->createCalendar($data);

        $this->assertDatabaseHas('calendars_dental_examinations', [
            'calendar_uuid' => $calendar->uuid,
            'dental_examination_uuid' => $dentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateCalendarSyncsDentalExaminationsReplacingPrevious(): void
    {
        $calendar = Calendar::factory()->create();
        $oldDentalExamination = DentalExamination::factory()->create();
        $newDentalExamination = DentalExamination::factory()->create();
        $calendar->dentalExaminations()->sync([$oldDentalExamination->uuid]);

        $this->service->updateCalendar($calendar, ['dental_examinations' => [$newDentalExamination->uuid]]);

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
    public function testAssignUsersRecordsAuditEntryWhenAuthenticated(): void
    {
        $actor = User::factory()->create();
        Auth::setUser($actor);

        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();

        $this->service->assignUsers($calendar, ['users' => [$user->uuid]]);

        $audit = Audit::query()
            ->where('auditable_id', $calendar->uuid)
            ->where('type', AuditableEventType::UPDATE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($actor->uuid, $audit->user_uuid);
        $this->assertSame(['users' => [$user->uuid]], $audit->change_to);
    }

    /**
     * @return void
     */
    public function testAssignUsersDoesNotRecordAuditWhenNotAuthenticated(): void
    {
        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();

        $this->service->assignUsers($calendar, ['users' => [$user->uuid]]);

        $this->assertSame(0, Audit::query()->where('auditable_id', $calendar->uuid)->count());
    }
}
