<?php

namespace Tests\Unit\Models;

use App\Enums\UserStatusType;
use App\Models\Department;
use App\Models\JobPosition;
use App\Models\MessageGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of UserTest
 */
final class UserTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function testGenderIsFemaleWhenPeselDigitIsEven(): void
    {
        $user = User::factory()->make(['pesel' => '90010112345']);

        $this->assertEquals('F', $user->gender);
    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function testGenderIsMaleWhenPeselDigitIsOdd(): void
    {
        $user = User::factory()->make(['pesel' => '90010112335']);

        $this->assertEquals('M', $user->gender);
    }

    /**
     * @return void
     */
    public function testGenderIsNullWhenPeselIsMissing(): void
    {
        $user = User::factory()->make(['pesel' => null]);

        $this->assertNull($user->gender);
    }

    /**
     * @return void
     */
    public function testGetNameReturnsFirstAndLastName(): void
    {
        $user = User::factory()->make(['first_name' => 'Jan', 'last_name' => 'Kowalski']);

        $this->assertSame('Jan Kowalski', $user->getName());
    }

    /**
     * @return void
     */
    public function testAvatarPathIsNullWithoutUploadedAvatar(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->avatar_path);
    }

    /**
     * @return void
     */
    public function testBackgroundPathIsNullWithoutUploadedBackground(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->background_path);
    }

    /**
     * @return void
     */
    public function testWhereTokenReturnsNullForUnknownToken(): void
    {
        $this->assertNull(User::whereToken('unknown-token'));
    }

    /**
     * @return void
     */
    public function testWhereTokenReturnsOwningUser(): void
    {
        $user = User::factory()->create();
        $plainTextToken = $user->createToken('test-token')->plainTextToken;

        $resolved = User::whereToken($plainTextToken);

        $this->assertNotNull($resolved);
        $this->assertTrue($resolved->is($user));
    }

    /**
     * @return void
     */
    public function testIsOnlineReturnsNonActiveWithoutValidToken(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserStatusType::NON_ACTIVE, $user->isOnline());
    }

    /**
     * @return void
     */
    public function testIsOnlineReturnsActiveWithValidToken(): void
    {
        $user = User::factory()->create();
        $user->createToken('test-token');

        $this->assertSame(UserStatusType::ACTIVE, $user->isOnline());
    }

    /**
     * @return void
     */
    public function testJobPositionReturnsAssignedJobPosition(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $user = User::factory()->create(['job_position_uuid' => $jobPosition->uuid]);

        $this->assertTrue($user->jobPosition->is($jobPosition));
    }

    /**
     * @return void
     */
    public function testMessageGroupsReturnsAttachedGroups(): void
    {
        $user = User::factory()->create();
        $group = MessageGroup::factory()->create();

        $user->messageGroups()->attach($group->uuid);

        $this->assertTrue($user->messageGroups->contains($group));
    }

    /**
     * @return void
     */
    public function testUserGroupsReturnsAttachedGroupsWithIsManagerPivot(): void
    {
        $user = User::factory()->create();
        $userGroup = UserGroup::factory()->create();

        $user->userGroups()->attach($userGroup->uuid, ['is_manager' => true]);

        $attached = $user->userGroups->first();
        $this->assertTrue($attached->is($userGroup));
        $this->assertTrue((bool) $attached->pivot->is_manager);
    }

    /**
     * @return void
     */
    public function testRolesReturnsAttachedRoles(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->roles()->attach($role->uuid);

        $this->assertTrue($user->roles->contains($role));
    }

    /**
     * @return void
     */
    public function testDepartmentsReturnsAttachedDepartments(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $user->departments()->attach($department->uuid);

        $this->assertTrue($user->departments->contains($department));
    }
}
