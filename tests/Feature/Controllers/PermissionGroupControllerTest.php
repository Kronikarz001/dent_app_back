<?php

namespace Tests\Feature\Controllers;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
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
    public function testStoreAndUpdatePersistDescription(): void
    {
        $client = $this->callApiWithLoggedUser();

        $uuid = $client->postJson(route('permission-group.store'), ['name' => 'Administrator', 'description' => 'Pełny dostęp'])
            ->assertCreated()
            ->assertJsonPath('description', 'Pełny dostęp')
            ->json('uuid');

        $client->putJson(route('permission-group.update', ['permissionGroup' => $uuid]), ['name' => 'Administrator', 'description' => null])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_groups', ['uuid' => $uuid, 'description' => null]);
    }

    /**
     * @return void
     */
    public function testStoreRejectsTooLongDescription(): void
    {
        $this->callApiWithLoggedUser()
            ->postJson(route('permission-group.store'), ['name' => 'Recepcja', 'description' => str_repeat('a', 1001)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');
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

    /**
     * @param PermissionGroup $group
     * @param User|Role $assignable
     * @param string|null $expiresAt
     * @return PermissionAssignment
     */
    private function grantGroup(PermissionGroup $group, User|Role $assignable, ?string $expiresAt = null): PermissionAssignment
    {
        return PermissionAssignment::query()->create([
            'grantable_type' => PermissionGroup::class,
            'grantable_id' => $group->uuid,
            'assignable_type' => $assignable::class,
            'assignable_id' => $assignable->uuid,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return void
     */
    public function testIndexIncludesDirectlyAssignedActiveUsers(): void
    {
        $group = PermissionGroup::factory()->create();
        $assigned = User::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);
        $this->grantGroup($group, $assigned);
        $this->grantGroup($group, User::factory()->create(), now()->subDay()->toDateTimeString());
        $this->grantGroup($group, Role::factory()->create());

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('permission-group.index'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data.0.users');
        $response->assertJsonPath('data.0.users.0.uuid', $assigned->uuid);
        $response->assertJsonPath('data.0.users.0.first_name', 'Jan');
        $response->assertJsonPath('data.0.users.0.last_name', 'Kowalski');
    }

    /**
     * @return void
     */
    public function testShowIncludesUsers(): void
    {
        $group = PermissionGroup::factory()->create();
        $user = User::factory()->create();
        $this->grantGroup($group, $user);

        $this->callApiWithLoggedUser()
            ->getJson(route('permission-group.show', ['permissionGroup' => $group->uuid]))
            ->assertOk()
            ->assertJsonPath('users.0.uuid', $user->uuid);
    }

    /**
     * @return void
     */
    public function testAssignUsersReplacesAssignedUsers(): void
    {
        $group = PermissionGroup::factory()->create();
        $removed = User::factory()->create();
        $kept = User::factory()->create();
        $added = User::factory()->create();
        $this->grantGroup($group, $removed);
        $this->grantGroup($group, $kept);

        $this->callApiWithLoggedUser()
            ->patchJson(route('permission-group.assignUsers', ['permissionGroup' => $group->uuid]), [
                'users' => [$kept->uuid, $added->uuid],
            ])
            ->assertNoContent();

        $this->assertEqualsCanonicalizing(
            [$kept->uuid, $added->uuid],
            $group->users()->pluck('users.uuid')->all()
        );
        $this->assertDatabaseMissing('permission_assignments', ['assignable_id' => $removed->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignUsersKeepsGrantsOfOtherAssignables(): void
    {
        $group = PermissionGroup::factory()->create();
        $role = Role::factory()->create();
        $this->grantGroup($group, $role);

        $this->callApiWithLoggedUser()
            ->patchJson(route('permission-group.assignUsers', ['permissionGroup' => $group->uuid]), ['users' => []])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', ['grantable_id' => $group->uuid, 'assignable_id' => $role->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignUsersRenewsExpiredAssignment(): void
    {
        $group = PermissionGroup::factory()->create();
        $user = User::factory()->create();
        $this->grantGroup($group, $user, now()->subDay()->toDateTimeString());

        $this->callApiWithLoggedUser()
            ->patchJson(route('permission-group.assignUsers', ['permissionGroup' => $group->uuid]), ['users' => [$user->uuid]])
            ->assertNoContent();

        $this->assertSame([$user->uuid], $group->users()->pluck('users.uuid')->all());
        $this->assertDatabaseCount('permission_assignments', 1);
    }

    /**
     * @return void
     */
    public function testAssignUsersValidatesPayload(): void
    {
        $group = PermissionGroup::factory()->create();
        $client = $this->callApiWithLoggedUser();
        $route = route('permission-group.assignUsers', ['permissionGroup' => $group->uuid]);

        $client->patchJson($route, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('users');
        $client->patchJson($route, ['users' => ['019e99cf-9ffe-70a8-9b4c-8b889d28eeff']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('users.0');
    }
}
