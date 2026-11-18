<?php

namespace Tests\Feature\Services;

use App\Exceptions\PermissionDeniedException;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\User;
use App\Services\PermissionGroupServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of PermissionGroupServiceTest
 */
class PermissionGroupServiceTest extends TestCase
{
    /**
     * @var PermissionGroupServiceInterface|Application|mixed|object
     */
    private PermissionGroupServiceInterface $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PermissionGroupServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetPermissionGroupsReturnsPaginatedResults(): void
    {
        PermissionGroup::factory()->count(3)->create();

        $result = $this->service->getPermissionGroups();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testCreatePermissionGroupPersistsToDatabase(): void
    {
        $group = $this->service->createPermissionGroup(['name' => 'Kadry']);

        $this->assertInstanceOf(PermissionGroup::class, $group);
        $this->assertDatabaseHas('permission_groups', ['uuid' => $group->uuid, 'name' => 'Kadry']);
    }

    /**
     * @return void
     */
    public function testUpdatePermissionGroupPersistsChanges(): void
    {
        $group = PermissionGroup::factory()->create();

        $result = $this->service->updatePermissionGroup($group, ['name' => 'Nowa nazwa']);

        $this->assertSame('Nowa nazwa', $result->name);
    }

    /**
     * @return void
     */
    public function testDeletePermissionGroupRemovesFromDatabase(): void
    {
        $group = PermissionGroup::factory()->create();

        $this->service->deletePermissionGroup($group);

        $this->assertDatabaseMissing('permission_groups', ['uuid' => $group->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsReplacesPreviousAssignments(): void
    {
        $group = PermissionGroup::factory()->create();
        $oldPermission = Permission::factory()->create();
        $newPermission = Permission::factory()->create();

        $this->service->assignPermissions($group, ['permissions' => [$oldPermission->uuid]]);
        $this->service->assignPermissions($group, ['permissions' => [$newPermission->uuid]]);

        $this->assertDatabaseMissing('permission_group_permission', [
            'permission_group_uuid' => $group->uuid,
            'permission_uuid' => $oldPermission->uuid,
        ]);
        $this->assertDatabaseHas('permission_group_permission', [
            'permission_group_uuid' => $group->uuid,
            'permission_uuid' => $newPermission->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignUsersByNonAdminWithoutGroupThrows(): void
    {
        $group = PermissionGroup::factory()->create();
        $target = User::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => false]));

        $this->expectException(PermissionDeniedException::class);

        try {
            $this->service->assignUsers($group, ['users' => [$target->uuid]]);
        } finally {
            $this->assertDatabaseMissing('permission_assignments', ['assignable_id' => $target->uuid]);
        }
    }

    /**
     * @return void
     */
    public function testAssignUsersByNonAdminHoldingGroupGrantsIt(): void
    {
        $group = PermissionGroup::factory()->create();
        $actor = User::factory()->create(['is_admin' => false]);
        $target = User::factory()->create();
        PermissionAssignment::query()->create([
            'grantable_type' => PermissionGroup::class,
            'grantable_id' => $group->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $actor->uuid,
        ]);
        $this->actingAs($actor);

        $this->service->assignUsers($group, ['users' => [$actor->uuid, $target->uuid]]);

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_id' => $group->uuid,
            'assignable_id' => $target->uuid,
            'granted_by' => $actor->uuid,
        ]);
    }
}
