<?php

namespace Tests\Unit\Factories;

use App\Models\Material;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MaterialFactoryTest
 */
final class MaterialFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testMaterialCreateByFactory(): void
    {
        $material = Material::factory()->create(['name' => 'Materiał testowy']);

        $this->assertEquals('Materiał testowy', $material->name);
    }
}
