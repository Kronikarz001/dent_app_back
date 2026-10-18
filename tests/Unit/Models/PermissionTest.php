<?php

namespace Tests\Unit\Models;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionTest
 */
final class PermissionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGetNameAttributeCombinesResourceAndType(): void
    {
        $permission = Permission::factory()->create(['resource' => 'test-permission-resource', 'type' => PermissionType::EDIT]);

        $this->assertSame('test-permission-resource.edit', $permission->name);
    }

    /**
     * @return void
     */
    public function testPermissionGroupsReturnsAttachedGroups(): void
    {
        $permission = Permission::factory()->create();
        $group = PermissionGroup::factory()->create();

        $permission->permissionGroups()->attach($group->uuid);

        $this->assertTrue($permission->permissionGroups->contains($group));
    }
}
