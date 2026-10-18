<?php

namespace Tests\Unit\Models;

use App\Models\JobPosition;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserGroupTest
 */
final class UserGroupTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUsersReturnsAttachedUsersWithIsManagerPivot(): void
    {
        $userGroup = UserGroup::factory()->create();
        $user = User::factory()->create();

        $userGroup->users()->attach($user->uuid, ['is_manager' => true]);

        $attached = $userGroup->users->first();
        $this->assertTrue($attached->is($user));
        $this->assertTrue((bool) $attached->pivot->is_manager);
    }

    /**
     * @return void
     */
    public function testRolesReturnsAttachedRoles(): void
    {
        $userGroup = UserGroup::factory()->create();
        $role = Role::factory()->create();

        $userGroup->roles()->attach($role->uuid);

        $this->assertTrue($userGroup->roles->contains($role));
    }

    /**
     * @return void
     */
    public function testJobPositionsReturnsAttachedJobPositions(): void
    {
        $userGroup = UserGroup::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $userGroup->jobPositions()->attach($jobPosition->uuid);

        $this->assertTrue($userGroup->jobPositions->contains($jobPosition));
    }
}
