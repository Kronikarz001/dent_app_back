<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Models\UserGroup;
use Tests\TestCase;

/**
 * Summary of UserGroupControllerTest
 */
class UserGroupControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        UserGroup::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('user-group.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('user-group.store'), ['name' => 'Recepcja']);

        $response->assertCreated();
        $this->assertDatabaseHas('user_groups', ['name' => 'Recepcja']);
    }

    /**
     * @return void
     */
    public function testUpdateReturnsNoContentResponse(): void
    {
        $group = UserGroup::factory()->create();

        $this->callApiWithLoggedUser()
            ->putJson(route('user-group.update', ['userGroup' => $group->uuid]), ['name' => 'Zaktualizowana'])
            ->assertNoContent();

        $this->assertDatabaseHas('user_groups', ['uuid' => $group->uuid, 'name' => 'Zaktualizowana']);
    }

    /**
     * @return void
     */
    public function testDestroyReturnsNoContentResponse(): void
    {
        $group = UserGroup::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('user-group.destroy', ['userGroup' => $group->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($group);
    }

    /**
     * @return void
     */
    public function testAssignUsersSyncsMembers(): void
    {
        $group = UserGroup::factory()->create();
        $user = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user-group.assignUsers', ['userGroup' => $group->uuid]), ['users' => [$user->uuid]])
            ->assertNoContent();

        $this->assertDatabaseHas('user_group_user', [
            'user_group_uuid' => $group->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsCreatesDirectPermissionGrant(): void
    {
        $group = UserGroup::factory()->create();
        $permission = Permission::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user-group.assignPermissions', ['userGroup' => $group->uuid]), [
                'permissions' => [$permission->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => UserGroup::class,
            'assignable_id' => $group->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsCreatesPermissionGroupGrantWithExpiry(): void
    {
        $group = UserGroup::factory()->create();
        $permissionGroup = PermissionGroup::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user-group.assignPermissions', ['userGroup' => $group->uuid]), [
                'permission_groups' => [$permissionGroup->uuid],
                'expires_at' => '2030-01-01 00:00:00',
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => PermissionGroup::class,
            'grantable_id' => $permissionGroup->uuid,
            'assignable_type' => UserGroup::class,
            'assignable_id' => $group->uuid,
        ]);
    }
}
