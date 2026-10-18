<?php

namespace Tests\Unit\Models;

use App\Models\Calendar;
use App\Models\EmployeeSchedule;
use Tests\Unit\UnitTestCase;

/**
 * Summary of EmployeeScheduleTest
 */
final class EmployeeScheduleTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGlobalScopeExcludesAppointmentTypeRows(): void
    {
        $employeeRow = EmployeeSchedule::factory()->create();
        $appointment = Calendar::factory()->create();

        $this->assertNotNull(EmployeeSchedule::find($employeeRow->uuid));
        $this->assertNull(EmployeeSchedule::find($appointment->uuid));
    }

    /**
     * @return void
     */
    public function testSharesTableWithCalendar(): void
    {
        $employeeRow = EmployeeSchedule::factory()->create();

        $this->assertDatabaseHas('calendars', ['uuid' => $employeeRow->uuid]);
    }
}
