<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionGroupTest
 */
final class PermissionGroupTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPermissionsReturnsAttachedPermissions(): void
    {
        $group = PermissionGroup::factory()->create();
        $permission = Permission::factory()->create();

        $group->permissions()->attach($permission->uuid);

        $this->assertTrue($group->permissions->contains($permission));
    }
}
