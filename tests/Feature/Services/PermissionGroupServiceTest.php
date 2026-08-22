<?php

namespace Tests\Feature\Services;

use App\Models\Permission;
use App\Models\PermissionGroup;
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
}
