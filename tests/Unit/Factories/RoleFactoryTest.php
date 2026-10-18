<?php

namespace Tests\Unit\Factories;

use App\Models\Role;
use Tests\Unit\UnitTestCase;

/**
 * Summary of RoleFactoryTest
 */
final class RoleFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRoleCreateByFactory(): void
    {
        $role = Role::factory()->create(['name' => 'Rola testowa']);

        $this->assertEquals('Rola testowa', $role->name);
    }
}
