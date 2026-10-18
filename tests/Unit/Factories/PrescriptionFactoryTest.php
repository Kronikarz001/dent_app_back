<?php

namespace Tests\Unit\Factories;

use App\Models\Prescription;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PrescriptionFactoryTest
 */
final class PrescriptionFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPrescriptionCreateByFactory(): void
    {
        $prescription = Prescription::factory()->create(['name' => 'Recepta testowa']);

        $this->assertEquals('Recepta testowa', $prescription->name);
    }
}
