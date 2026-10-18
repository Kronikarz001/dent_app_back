<?php

namespace Tests\Unit\Models;

use App\Models\DentalExamination;
use App\Models\Material;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MaterialTest
 */
final class MaterialTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDentalExaminationsReturnsAttachedExaminations(): void
    {
        $material = Material::factory()->create();
        $examination = DentalExamination::factory()->create();

        $material->dentalExaminations()->attach($examination->uuid);

        $this->assertTrue($material->dentalExaminations->contains($examination));
    }
}
