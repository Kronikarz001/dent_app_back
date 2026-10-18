<?php

namespace Tests\Unit\Models;

use App\Models\Calendar;
use App\Models\Patient;
use App\Models\Photo;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PatientTest
 */
final class PatientTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGetNameReturnsFirstAndLastName(): void
    {
        $patient = Patient::factory()->make(['first_name' => 'Jan', 'last_name' => 'Kowalski']);

        $this->assertSame('Jan Kowalski', $patient->getName());
    }

    /**
     * @return void
     */
    public function testDoctorReturnsAssignedUser(): void
    {
        $doctor = User::factory()->create();
        $patient = Patient::factory()->create(['doctor_uuid' => $doctor->uuid]);

        $this->assertTrue($patient->doctor->is($doctor));
    }

    /**
     * @return void
     */
    public function testDoctorIsNullWhenNotAssigned(): void
    {
        $patient = Patient::factory()->create(['doctor_uuid' => null]);

        $this->assertNull($patient->doctor);
    }

    /**
     * @return void
     */
    public function testCalendarsReturnsAttachedCalendars(): void
    {
        $patient = Patient::factory()->create();
        $calendar = Calendar::factory()->create();

        $patient->calendars()->attach($calendar->uuid);

        $this->assertTrue($patient->calendars->contains($calendar));
    }

    /**
     * @return void
     */
    public function testPhotosReturnsPatientsPhotos(): void
    {
        $patient = Patient::factory()->create();
        $photo = Photo::factory()->create(['patient_uuid' => $patient->uuid]);
        Photo::factory()->create();

        $this->assertTrue($patient->photos->contains($photo));
        $this->assertCount(1, $patient->photos);
    }
}
