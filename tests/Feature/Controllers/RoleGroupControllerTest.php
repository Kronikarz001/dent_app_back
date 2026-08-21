<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\Role;
use App\Models\RoleGroup;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of RoleGroupControllerTest
 */
class RoleGroupControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        RoleGroup::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('role-group.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('role-group.store'), ['name' => 'Zarząd']);

        $response->assertCreated();
        $this->assertDatabaseHas('role_groups', ['name' => 'Zarząd']);
    }

    /**
     * @return void
     */
    public function testAssignRolesSyncsRoles(): void
    {
        $roleGroup = RoleGroup::factory()->create();
        $role = Role::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('role-group.assignRoles', ['roleGroup' => $roleGroup->uuid]), ['roles' => [$role->uuid]])
            ->assertNoContent();

        $this->assertDatabaseHas('role_group_role', ['role_group_uuid' => $roleGroup->uuid, 'role_uuid' => $role->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignUsersSetsManagerFlag(): void
    {
        $roleGroup = RoleGroup::factory()->create();
        $manager = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('role-group.assignUsers', ['roleGroup' => $roleGroup->uuid]), [
                'users' => [['uuid' => $manager->uuid, 'is_manager' => true]],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('role_group_user', ['role_group_uuid' => $roleGroup->uuid, 'user_uuid' => $manager->uuid, 'is_manager' => true]);
    }

    /**
     * @return void
     */
    public function testDelegateAllowsManagerToShareOwnPermissionWithRoleGroupMemberViaRole(): void
    {
        $roleGroup = RoleGroup::factory()->create();
        $manager = User::factory()->create();
        $roleGroup->users()->attach($manager->uuid, ['is_manager' => true]);

        $role = Role::factory()->create();
        $roleGroup->roles()->attach($role->uuid);
        $member = User::factory()->create();
        $role->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $manager->uuid,
        ]);

        $this->callApiWithLoggedUser($manager)
            ->postJson(route('role-group.delegate', ['roleGroup' => $roleGroup->uuid]), [
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
        $roleGroup = RoleGroup::factory()->create();
        $notManager = User::factory()->create();
        $member = User::factory()->create();
        $roleGroup->users()->attach($notManager->uuid, ['is_manager' => false]);
        $roleGroup->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();

        $response = $this->callApiWithLoggedUser($notManager)
            ->postJson(route('role-group.delegate', ['roleGroup' => $roleGroup->uuid]), [
                'user_uuid' => $member->uuid,
                'permission_uuid' => $permission->uuid,
            ]);

        $response->assertStatus(403);
    }
}
