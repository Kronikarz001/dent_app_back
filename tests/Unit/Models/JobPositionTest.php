<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\User;
use App\Models\UserGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of JobPositionTest
 */
final class JobPositionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUsersReturnsUsersWithThisJobPosition(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $user = User::factory()->create(['job_position_uuid' => $jobPosition->uuid]);
        User::factory()->create();

        $this->assertTrue($jobPosition->users->contains($user));
        $this->assertCount(1, $jobPosition->users);
    }

    /**
     * @return void
     */
    public function testDepartmentsReturnsAttachedDepartments(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $department = Department::factory()->create();

        $jobPosition->departments()->attach($department->uuid);

        $this->assertTrue($jobPosition->departments->contains($department));
    }

    /**
     * @return void
     */
    public function testUserGroupsReturnsAttachedUserGroups(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $userGroup = UserGroup::factory()->create();

        $jobPosition->userGroups()->attach($userGroup->uuid);

        $this->assertTrue($jobPosition->userGroups->contains($userGroup));
    }

    /**
     * @return void
     */
    public function testDentistNameConstantMatchesExpectedValue(): void
    {
        $this->assertSame('Stanowisko lekarza stomatologa', JobPosition::DENTIST_NAME);
    }
}
