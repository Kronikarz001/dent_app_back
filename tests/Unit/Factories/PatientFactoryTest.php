<?php

namespace Tests\Unit\Factories;

use App\Models\Patient;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PatientFactoryTest
 */
final class PatientFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPatientCreateByFactory(): void
    {
        $patient = Patient::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);

        $this->assertEquals('Jan', $patient->first_name);
        $this->assertEquals('Kowalski', $patient->last_name);
    }
}
