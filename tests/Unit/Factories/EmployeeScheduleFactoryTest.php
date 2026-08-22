<?php

namespace Tests\Unit\Factories;

use App\Models\EmployeeSchedule;
use Tests\Unit\UnitTestCase;

/**
 * Summary of EmployeeScheduleFactoryTest
 */
final class EmployeeScheduleFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testEmployeeScheduleCreateByFactory(): void
    {
        $employeeSchedule = EmployeeSchedule::factory()->create(['name' => 'Dyżur poranny']);

        $this->assertEquals('Dyżur poranny', $employeeSchedule->name);
    }
}
