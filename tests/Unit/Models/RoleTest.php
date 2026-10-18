<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of RoleTest
 */
final class RoleTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUsersReturnsAttachedUsersWithIsManagerPivot(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $role->users()->attach($user->uuid, ['is_manager' => true]);

        $attached = $role->users->first();
        $this->assertTrue($attached->is($user));
        $this->assertTrue((bool) $attached->pivot->is_manager);
    }

    /**
     * @return void
     */
    public function testDepartmentsReturnsAttachedDepartments(): void
    {
        $role = Role::factory()->create();
        $department = Department::factory()->create();

        $role->departments()->attach($department->uuid);

        $this->assertTrue($role->departments->contains($department));
    }

    /**
     * @return void
     */
    public function testUserGroupsReturnsAttachedUserGroups(): void
    {
        $role = Role::factory()->create();
        $userGroup = UserGroup::factory()->create();

        $role->userGroups()->attach($userGroup->uuid);

        $this->assertTrue($role->userGroups->contains($userGroup));
    }
}
