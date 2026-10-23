<?php

namespace Tests\Unit\Mappers;

use App\Enums\VisitStatus;
use App\Mappers\PatientVisitMapper;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Summary of PatientVisitMapperTest
 */
class PatientVisitMapperTest extends TestCase
{
    /**
     * @return void
     */
    public function testMapsCalendarToVisitDto(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $doctor = User::factory()->create();
        $examination = DentalExamination::factory()->create();
        $calendar = Calendar::factory()->create(['date' => '2026-10-02', 'start_time' => '12:00:00', 'no_show' => false]);
        $calendar->users()->attach($doctor->uuid);
        $calendar->dentalExaminations()->attach($examination->uuid);

        $dto = (new PatientVisitMapper)->map($calendar->fresh());

        $this->assertSame($calendar->uuid, $dto->getUuid());
        $this->assertSame('2026-10-02 12:00:00', $dto->getDate()->toDateTimeString());
        $this->assertTrue($dto->getDoctor()->is($doctor));
        $this->assertSame([$examination->uuid], $dto->getTreatments()->pluck('uuid')->all());
        $this->assertSame(VisitStatus::COMPLETED, $dto->getStatus());
    }

    /**
     * @return void
     */
    public function testMapsVisitWithoutDoctorAndTreatments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $calendar = Calendar::factory()->create(['date' => '2026-10-28', 'start_time' => '10:30:00', 'no_show' => false]);

        $dto = (new PatientVisitMapper)->map($calendar);

        $this->assertNull($dto->getDoctor());
        $this->assertTrue($dto->getTreatments()->isEmpty());
        $this->assertSame(VisitStatus::SCHEDULED, $dto->getStatus());
    }
}
