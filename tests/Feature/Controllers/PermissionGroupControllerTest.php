<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Tests\TestCase;

/**
 * Summary of PermissionGroupControllerTest
 */
class PermissionGroupControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        PermissionGroup::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission-group.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('permission-group.store'), ['name' => 'Recepcja']);

        $response->assertCreated();
        $this->assertDatabaseHas('permission_groups', ['name' => 'Recepcja']);
    }

    /**
     * @return void
     */
    public function testUpdateReturnsNoContentResponse(): void
    {
        $group = PermissionGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('permission-group.update', ['permissionGroup' => $group->uuid]), ['name' => 'Zaktualizowana']);

        $response->assertNoContent();
        $this->assertDatabaseHas('permission_groups', ['uuid' => $group->uuid, 'name' => 'Zaktualizowana']);
    }

    /**
     * @return void
     */
    public function testDestroyReturnsNoContentResponse(): void
    {
        $group = PermissionGroup::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('permission-group.destroy', ['permissionGroup' => $group->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($group);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsSyncsPermissions(): void
    {
        $group = PermissionGroup::factory()->create();
        $permission = Permission::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('permission-group.assignPermissions', ['permissionGroup' => $group->uuid]), [
                'permissions' => [$permission->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_group_permission', [
            'permission_group_uuid' => $group->uuid,
            'permission_uuid' => $permission->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsWithNonExistentUuidReturnsValidationError(): void
    {
        $group = PermissionGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->patchJson(route('permission-group.assignPermissions', ['permissionGroup' => $group->uuid]), [
                'permissions' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff'],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowIncludesPermissions(): void
    {
        $group = PermissionGroup::factory()->create();
        $permission = Permission::factory()->create();
        $group->permissions()->attach($permission->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission-group.show', ['permissionGroup' => $group->uuid]));

        $response->assertOk();
        $response->assertJsonPath('permissions.0.uuid', $permission->uuid);
    }
}
