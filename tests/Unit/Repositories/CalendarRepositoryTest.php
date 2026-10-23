<?php

namespace Tests\Unit\Repositories;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\EmployeeSchedule;
use App\Models\JobPosition;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\CalendarRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    /**
     * @return void
     */
    public function testFindLastVisitForPatientSkipsNoShowsAndFutureVisits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $patient = Patient::factory()->create();
        $expected = $this->createVisitFor($patient, '2026-10-21', '11:00');
        $this->createVisitFor($patient, '2026-10-20', '09:00');
        $this->createVisitFor($patient, '2026-10-21', '11:30', true);
        $this->createVisitFor($patient, '2026-10-21', '13:00');
        $this->createVisitFor(Patient::factory()->create(), '2026-10-21', '11:45');

        $this->assertTrue($this->repository->findLastVisitForPatient($patient)->is($expected));
    }

    /**
     * @return void
     */
    public function testFindNextVisitForPatientReturnsNearestUpcomingVisit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $patient = Patient::factory()->create();
        $this->createVisitFor($patient, '2026-10-21', '11:00');
        $this->createVisitFor($patient, '2026-10-28', '10:30');
        $expected = $this->createVisitFor($patient, '2026-10-21', '12:30');

        $this->assertTrue($this->repository->findNextVisitForPatient($patient)->is($expected));
    }

    /**
     * @return void
     */
    public function testPastAndUpcomingVisitsAreSplitInClinicTimezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 10:30:00', 'UTC'));
        $patient = Patient::factory()->create();
        $last = $this->createVisitFor($patient, '2026-10-21', '12:00');
        $next = $this->createVisitFor($patient, '2026-10-21', '13:00');

        $this->assertTrue($this->repository->findLastVisitForPatient($patient)->is($last));
        $this->assertTrue($this->repository->findNextVisitForPatient($patient)->is($next));
    }

    /**
     * @return void
     */
    public function testClinicDateIsUsedAroundMidnight(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 23:30:00', 'UTC'));
        $patient = Patient::factory()->create();
        $last = $this->createVisitFor($patient, '2026-10-22', '01:00');
        $next = $this->createVisitFor($patient, '2026-10-22', '02:00');

        $this->assertTrue($this->repository->findLastVisitForPatient($patient)->is($last));
        $this->assertTrue($this->repository->findNextVisitForPatient($patient)->is($next));
    }

    /**
     * @return void
     */
    public function testFindNextVisitForPatientReturnsNullWithoutUpcomingVisits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $patient = Patient::factory()->create();
        $this->createVisitFor($patient, '2026-10-01', '10:00');

        $this->assertNull($this->repository->findNextVisitForPatient($patient));
    }

    /**
     * @return void
     */
    public function testFindLastNoShowForPatientReturnsLatestNoShow(): void
    {
        $patient = Patient::factory()->create();
        $this->createVisitFor($patient, '2026-07-01', '09:00', true);
        $expected = $this->createVisitFor($patient, '2026-08-14', '09:00', true);
        $this->createVisitFor($patient, '2026-09-01', '09:00');

        $this->assertTrue($this->repository->findLastNoShowForPatient($patient)->is($expected));
    }

    /**
     * @return void
     */
    public function testCountForPatientCountsAllAndNoShowVisits(): void
    {
        $patient = Patient::factory()->create();
        $this->createVisitFor($patient, '2026-07-01', '09:00', true);
        $this->createVisitFor($patient, '2026-08-01', '09:00');
        $this->createVisitFor($patient, '2026-09-01', '09:00');
        $this->createVisitFor(Patient::factory()->create(), '2026-09-01', '09:00', true);

        $this->assertSame(3, $this->repository->countForPatient($patient));
        $this->assertSame(1, $this->repository->countForPatient($patient, true));
    }

    /**
     * @return void
     */
    public function testVisitTodayWithoutStartTimeIsTreatedAsAlreadyStarted(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $patient = Patient::factory()->create();
        $visit = $this->createVisitFor($patient, '2026-10-21', '09:00');
        $visit->update(['start_time' => null]);

        $this->assertTrue($this->repository->findLastVisitForPatient($patient)->is($visit));
        $this->assertNull($this->repository->findNextVisitForPatient($patient));
    }

    /**
     * @return void
     */
    public function testCountForPatientIgnoresEmployeeScheduleRows(): void
    {
        $patient = Patient::factory()->create();
        $this->createVisitFor($patient, '2026-09-01', '09:00');
        $scheduleRow = EmployeeSchedule::factory()->create(['date' => '2026-09-02']);
        DB::table('calendar_users')->insert([
            'calendar_uuid' => $scheduleRow->uuid,
            'userable_id' => $patient->uuid,
            'userable_type' => $patient->getMorphClass(),
        ]);

        $this->assertSame(1, $this->repository->countForPatient($patient));
    }

    /**
     * @return void
     */
    public function testPatientVisitsEagerLoadDoctorsWithJobPosition(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create(['job_position_uuid' => JobPosition::factory()->create()->uuid]);
        $visit = $this->createVisitFor($patient, '2026-10-28', '10:30');
        $visit->users()->attach($doctor->uuid);

        $found = $this->repository->findNextVisitForPatient($patient);

        $this->assertTrue($found->relationLoaded('users'));
        $this->assertTrue($found->users->first()->is($doctor));
        $this->assertTrue($found->users->first()->relationLoaded('jobPosition'));
    }

    /**
     * @return void
     */
    public function testFindVisitsForPatientReturnsOnlyPatientVisitsNewestFirstWithRelations(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create();
        $examination = DentalExamination::factory()->create(['name' => 'Przegląd stomatologiczny']);

        $oldest = $this->createVisitFor($patient, '2026-01-10', '09:00');
        $newest = $this->createVisitFor($patient, '2026-10-02', '12:00');
        $sameDayEarlier = $this->createVisitFor($patient, '2026-10-02', '08:00', true);
        $newest->users()->attach($doctor->uuid);
        $newest->dentalExaminations()->attach($examination->uuid);
        $this->createVisitFor(Patient::factory()->create(), '2026-10-05', '10:00');
        $schedule = EmployeeSchedule::factory()->create(['date' => '2026-10-03']);
        DB::table('calendar_users')->insert([
            'calendar_uuid' => $schedule->uuid,
            'userable_id' => $patient->uuid,
            'userable_type' => $patient->getMorphClass(),
        ]);

        $result = $this->repository->findVisitsForPatient($patient);

        $this->assertSame([$newest->uuid, $sameDayEarlier->uuid, $oldest->uuid], $result->getCollection()->pluck('uuid')->all());
        $this->assertSame(3, $result->total());
        $this->assertTrue($result->getCollection()->first()->relationLoaded('users'));
        $this->assertTrue($result->getCollection()->first()->relationLoaded('dentalExaminations'));
        $this->assertSame('Przegląd stomatologiczny', $result->getCollection()->first()->dentalExaminations->first()->name);
        $this->assertTrue($result->getCollection()->first()->users->first()->is($doctor));
    }

    /**
     * @param Patient $patient
     * @param string $date
     * @param string $startTime
     * @param bool $noShow
     * @return Calendar
     */
    private function createVisitFor(Patient $patient, string $date, string $startTime, bool $noShow = false): Calendar
    {
        $calendar = Calendar::factory()->create([
            'date' => $date,
            'start_time' => $startTime,
            'no_show' => $noShow,
        ]);
        $calendar->patients()->attach($patient->uuid);

        return $calendar;
    }
}
