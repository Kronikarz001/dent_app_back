<?php

namespace Tests\Unit\Models;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\Material;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DentalExaminationTest
 */
final class DentalExaminationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testMaterialsReturnsAttachedMaterials(): void
    {
        $examination = DentalExamination::factory()->create();
        $material = Material::factory()->create();

        $examination->materials()->attach($material->uuid);

        $this->assertTrue($examination->materials->contains($material));
    }

    /**
     * @return void
     */
    public function testCalendarsReturnsAttachedCalendars(): void
    {
        $examination = DentalExamination::factory()->create();
        $calendar = Calendar::factory()->create();

        $examination->calendars()->attach($calendar->uuid);

        $this->assertTrue($examination->calendars->contains($calendar));
    }
}
