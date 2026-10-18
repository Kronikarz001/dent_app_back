<?php

namespace Tests\Unit\Factories;

use App\Models\Medicine;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MedicineFactoryTest
 */
final class MedicineFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testMedicineCreateByFactory(): void
    {
        $medicine = Medicine::factory()->create(['name' => 'Apap']);

        $this->assertEquals('Apap', $medicine->name);
    }
}
