<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Role;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DepartmentTest
 */
final class DepartmentTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRolesReturnsAttachedRoles(): void
    {
        $department = Department::factory()->create();
        $role = Role::factory()->create();

        $department->roles()->attach($role->uuid);

        $this->assertTrue($department->roles->contains($role));
    }

    /**
     * @return void
     */
    public function testUsersReturnsAttachedUsersWithIsManagerPivot(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();

        $department->users()->attach($user->uuid, ['is_manager' => true]);

        $attached = $department->users->first();
        $this->assertTrue($attached->is($user));
        $this->assertTrue((bool) $attached->pivot->is_manager);
    }

    /**
     * @return void
     */
    public function testJobPositionsReturnsAttachedJobPositions(): void
    {
        $department = Department::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $department->jobPositions()->attach($jobPosition->uuid);

        $this->assertTrue($department->jobPositions->contains($jobPosition));
    }
}
