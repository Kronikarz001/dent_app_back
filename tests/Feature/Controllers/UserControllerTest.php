<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Summary of UserControllerTest
 */
class UserControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexUsersReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexUsersListReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexFiltersByExactFieldValue(): void
    {
        User::factory()->create(['first_name' => 'Zbigniew']);
        $target = User::factory()->create(['first_name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['user' => ['first_name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        User::factory()->create(['first_name' => 'Bartosz']);
        User::factory()->create(['first_name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['sort' => 'first_name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = User::factory()->create(['first_name' => 'Wyjatkowy']);
        User::factory()->create(['first_name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testShowUserReturnSuccessResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $user->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowUserDoesNotExposeAdminFlagsOfOtherUser(): void
    {
        $target = User::factory()->create(['is_admin' => true, 'is_superuser' => true]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $target->uuid]));

        $response->assertOk();
        $response->assertJsonMissingPath('is_admin');
        $response->assertJsonMissingPath('is_superuser');
    }

    /**
     * @return void
     */
    public function testShowUserReturnsStatusNonActiveWithoutValidToken(): void
    {
        $target = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $target->uuid]));

        $response->assertOk();
        $response->assertJsonPath('status', 'NON_ACTIVE');
    }

    /**
     * @return void
     */
    public function testShowUserReturnsStatusActiveWithFreshToken(): void
    {
        $target = User::factory()->create();
        $target->createToken('token');

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $target->uuid]));

        $response->assertOk();
        $response->assertJsonPath('status', 'ACTIVE');
    }

    /**
     * @return void
     */
    public function testStoreUserReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('user.store'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'example@mail',
                'private_email' => 'example_private@mail',
                'pesel' => '44051401359',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateUserReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('user.update', ['user' => $user->uuid]), [
                'first_name' => 'Updated',
                'last_name' => 'User',
                'email' => 'example_updated@mail',
                'pesel' => '44051401359',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('users', [
            'email' => 'example_updated@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateUserAssignsPrivateAndWorkPhoneNumbers(): void
    {
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('user.update', ['user' => $user->uuid]), [
                'private_phone_number' => '48500100200',
                'phone_number' => '48600200300',
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_uuid' => $user->uuid,
            'phoneable_type' => User::class,
            'number' => '48500100200',
            'type' => 'PRIVATE',
        ]);
        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_uuid' => $user->uuid,
            'phoneable_type' => User::class,
            'number' => '48600200300',
            'type' => 'WORK',
        ]);
    }

    /**
     * @return void
     */
    public function testShowUserReturnsPrivateAndWorkPhoneNumbers(): void
    {
        $user = User::factory()->create();
        $this->callApiWithLoggedUser()
            ->putJson(route('user.update', ['user' => $user->uuid]), [
                'private_phone_number' => '48500100200',
                'phone_number' => '48600200300',
            ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.show', ['user' => $user->uuid]));

        $response->assertOk();
        $response->assertJsonPath('private_phone_number', '48500100200');
        $response->assertJsonPath('phone_number', '48600200300');
    }

    /**
     * @return void
     */
    public function testUpdateUserRejectsInvalidPhoneNumber(): void
    {
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('user.update', ['user' => $user->uuid]), [
                'private_phone_number' => 'not-a-phone-number',
            ]);

        $response->assertUnprocessable();
    }

    /**
     * @return void
     */
    public function testDeleteUserReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->deleteJson(route('user.destroy', ['user' => $user->uuid]));
        $response->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'email' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testShowLoggedUserReturnSuccessResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.user-info'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowLoggedUserIncludesAllPermissionsForAdmin(): void
    {
        Permission::factory()->create(['resource' => 'permtest-widget', 'type' => 'view']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user.user-info'));

        $response->assertOk();
        $this->assertContains('permtest-widget.view', $response->json('permissions'));
    }

    /**
     * @return void
     */
    public function testShowLoggedUserIncludesOnlyGrantedPermissionsForNonAdmin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $granted = Permission::factory()->create(['resource' => 'permtest-granted', 'type' => 'view']);
        Permission::factory()->create(['resource' => 'permtest-notgranted', 'type' => 'view']);
        $userInfoPermission = Permission::where(['resource' => 'user', 'type' => 'view'])->firstOrFail();

        foreach ([$granted, $userInfoPermission] as $permission) {
            PermissionAssignment::create([
                'grantable_type' => Permission::class,
                'grantable_id' => $permission->uuid,
                'assignable_type' => User::class,
                'assignable_id' => $user->uuid,
            ]);
        }

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('user.user-info'));

        $response->assertOk();
        $permissions = $response->json('permissions');
        $this->assertContains('permtest-granted.view', $permissions);
        $this->assertNotContains('permtest-notgranted.view', $permissions);
    }

    /**
     * @return void
     */
    public function testEditPasswordReturnNoContentResponse(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertNoContent();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * @return void
     */
    public function testEditPasswordWithWrongCurrentPasswordReturnsValidationError(): void
    {
        $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

        $response = $this->callApiWithLoggedUser($user)
            ->patchJson(route('user.edit_password'), [
                'current_password' => 'WrongPassword!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testEditPasswordRequiresAuthentication(): void
    {
        $response = $this->patchJson(route('user.edit_password'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testAssignPermissionsCreatesDirectGrant(): void
    {
        $target = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user.assignPermissions', ['user' => $target->uuid]), [
                'permissions' => [$permission->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $target->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsForbiddenWhenActingUserLacksGrant(): void
    {
        $actingUser = User::factory()->create(['is_admin' => false]);
        $target = User::factory()->create();

        // acting user may manage users (passes the route/resource check),
        // but does not personally hold the "role" permission it is trying to grant
        $ownResourcePermission = Permission::where('resource', 'user')->where('type', 'edit')->firstOrFail();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $ownResourcePermission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $actingUser->uuid,
        ]);

        $unownedPermission = Permission::where('resource', 'role')->where('type', 'edit')->firstOrFail();

        $response = $this->callApiWithLoggedUser($actingUser)
            ->patchJson(route('user.assignPermissions', ['user' => $target->uuid]), [
                'permissions' => [$unownedPermission->uuid],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $unownedPermission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $target->uuid,
        ]);
    }
}
