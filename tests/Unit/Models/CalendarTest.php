<?php

namespace Tests\Unit\Models;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\EmployeeSchedule;
use App\Models\Patient;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CalendarTest
 */
final class CalendarTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGlobalScopeExcludesEmployeeTypeRows(): void
    {
        $appointment = Calendar::factory()->create();
        $employeeRow = EmployeeSchedule::factory()->create();

        $this->assertNotNull(Calendar::find($appointment->uuid));
        $this->assertNull(Calendar::find($employeeRow->uuid));
    }

    /**
     * @return void
     */
    public function testUsersReturnsAttachedUsers(): void
    {
        $calendar = Calendar::factory()->create();
        $user = User::factory()->create();

        $calendar->users()->attach($user->uuid);

        $this->assertTrue($calendar->users->contains($user));
    }

    /**
     * @return void
     */
    public function testPatientsReturnsAttachedPatients(): void
    {
        $calendar = Calendar::factory()->create();
        $patient = Patient::factory()->create();

        $calendar->patients()->attach($patient->uuid);

        $this->assertTrue($calendar->patients->contains($patient));
    }

    /**
     * @return void
     */
    public function testDentalExaminationsReturnsAttachedExaminations(): void
    {
        $calendar = Calendar::factory()->create();
        $examination = DentalExamination::factory()->create();

        $calendar->dentalExaminations()->attach($examination->uuid);

        $this->assertTrue($calendar->dentalExaminations->contains($examination));
    }
}
