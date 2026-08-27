<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of RoleControllerTest
 */
class RoleControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('role.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('role.store'), ['name' => 'Recepcjonistka']);

        $response->assertCreated();
        $this->assertDatabaseHas('roles', ['name' => 'Recepcjonistka']);
    }

    /**
     * @return void
     */
    public function testDestroyReturnsNoContentResponse(): void
    {
        $role = Role::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('role.destroy', ['role' => $role->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($role);
    }

    /**
     * @return void
     */
    public function testAssignUsersSetsManagerFlagPerUser(): void
    {
        $role = Role::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('role.assignUsers', ['role' => $role->uuid]), [
                'users' => [
                    ['uuid' => $manager->uuid, 'is_manager' => true],
                    ['uuid' => $member->uuid, 'is_manager' => false],
                ],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('role_user', ['role_uuid' => $role->uuid, 'user_uuid' => $manager->uuid, 'is_manager' => true]);
        $this->assertDatabaseHas('role_user', ['role_uuid' => $role->uuid, 'user_uuid' => $member->uuid, 'is_manager' => false]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsCreatesGrant(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('role.assignPermissions', ['role' => $role->uuid]), [
                'permissions' => [$permission->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => Role::class,
            'assignable_id' => $role->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsForbiddenWhenActingUserLacksGrant(): void
    {
        $actingUser = User::factory()->create(['is_admin' => false]);
        $role = Role::factory()->create();

        $ownResourcePermission = Permission::where('resource', 'role')->where('type', 'edit')->firstOrFail();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $ownResourcePermission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $actingUser->uuid,
        ]);

        $unownedPermission = Permission::where('resource', 'user')->where('type', 'edit')->firstOrFail();

        $response = $this->callApiWithLoggedUser($actingUser)
            ->patchJson(route('role.assignPermissions', ['role' => $role->uuid]), [
                'permissions' => [$unownedPermission->uuid],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $unownedPermission->uuid,
            'assignable_type' => Role::class,
            'assignable_id' => $role->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDelegateAllowsManagerToShareOwnPermissionWithRoleMember(): void
    {
        $role = Role::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();
        $role->users()->attach($manager->uuid, ['is_manager' => true]);
        $role->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $manager->uuid,
        ]);

        $this->callApiWithLoggedUser($manager)
            ->postJson(route('role.delegate', ['role' => $role->uuid]), [
                'user_uuid' => $member->uuid,
                'permission_uuid' => $permission->uuid,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $member->uuid,
            'granted_by' => $manager->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDelegateForbiddenForNonManager(): void
    {
        $role = Role::factory()->create();
        $notManager = User::factory()->create();
        $member = User::factory()->create();
        $role->users()->attach($notManager->uuid, ['is_manager' => false]);
        $role->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();

        $response = $this->callApiWithLoggedUser($notManager)
            ->postJson(route('role.delegate', ['role' => $role->uuid]), [
                'user_uuid' => $member->uuid,
                'permission_uuid' => $permission->uuid,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testDelegateForbiddenWhenManagerDoesNotHoldThePermission(): void
    {
        $role = Role::factory()->create();
        $manager = User::factory()->create();
        $member = User::factory()->create();
        $role->users()->attach($manager->uuid, ['is_manager' => true]);
        $role->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('role.delegate', ['role' => $role->uuid]), [
                'user_uuid' => $member->uuid,
                'permission_uuid' => $permission->uuid,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testDelegateForbiddenWhenTargetNotInRole(): void
    {
        $role = Role::factory()->create();
        $manager = User::factory()->create();
        $outsider = User::factory()->create();
        $role->users()->attach($manager->uuid, ['is_manager' => true]);

        $permission = Permission::factory()->create();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $manager->uuid,
        ]);

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('role.delegate', ['role' => $role->uuid]), [
                'user_uuid' => $outsider->uuid,
                'permission_uuid' => $permission->uuid,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testDelegateWithBothPermissionAndGroupReturnsValidationError(): void
    {
        $role = Role::factory()->create();
        $manager = User::factory()->create();
        $role->users()->attach($manager->uuid, ['is_manager' => true]);

        $permission = Permission::factory()->create();
        $group = PermissionGroup::factory()->create();

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('role.delegate', ['role' => $role->uuid]), [
                'user_uuid' => $manager->uuid,
                'permission_uuid' => $permission->uuid,
                'permission_group_uuid' => $group->uuid,
            ]);

        $response->assertStatus(422);
    }
}
