<?php

namespace Tests\Unit\Factories;

use App\Models\DentalExamination;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DentalExaminationFactoryTest
 */
final class DentalExaminationFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDentalExaminationCreateByFactory(): void
    {
        $dentalExamination = DentalExamination::factory()->create(['name' => 'Przegląd stomatologiczny']);

        $this->assertEquals('Przegląd stomatologiczny', $dentalExamination->name);
    }
}
