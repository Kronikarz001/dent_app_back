<?php

namespace Tests\Unit\Factories;

use App\Models\Department;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DepartmentFactoryTest
 */
final class DepartmentFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDepartmentCreateByFactory(): void
    {
        $department = Department::factory()->create(['name' => 'Dział testowy']);

        $this->assertEquals('Dział testowy', $department->name);
    }
}
