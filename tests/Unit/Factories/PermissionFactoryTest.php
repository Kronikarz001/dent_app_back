<?php

namespace Tests\Unit\Factories;

use App\Models\Permission;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionFactoryTest
 */
final class PermissionFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPermissionCreateByFactory(): void
    {
        $permission = Permission::factory()->create(['resource' => 'test-resource']);

        $this->assertEquals('test-resource', $permission->resource);
    }
}
