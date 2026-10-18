<?php

namespace Tests\Unit\Factories;

use App\Models\PermissionGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionGroupFactoryTest
 */
final class PermissionGroupFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPermissionGroupCreateByFactory(): void
    {
        $group = PermissionGroup::factory()->create(['name' => 'Grupa testowa']);

        $this->assertEquals('Grupa testowa', $group->name);
    }
}
