<?php

namespace Tests\Unit\Factories;

use App\Models\JobPosition;
use Tests\Unit\UnitTestCase;

/**
 * Summary of JobPositionFactoryTest
 */
final class JobPositionFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testJobPositionCreateByFactory(): void
    {
        $jobPosition = JobPosition::factory()->create(['name' => 'Stanowisko testowe']);

        $this->assertEquals('Stanowisko testowe', $jobPosition->name);
    }
}
