<?php

namespace Tests\Unit\Repositories;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use App\Repositories\PermissionRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of PermissionRepositoryTest
 *
 * Broader has-permission scenarios (via group/role/department/job position)
 * are already covered end-to-end in tests/Feature/Services/PermissionServiceTest.php.
 */
class PermissionRepositoryTest extends TestCase
{
    /**
     * @var PermissionRepositoryInterface
     */
    private PermissionRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PermissionRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsFalseWithoutGrant(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        Permission::factory()->create(['resource' => 'test-repo-resource', 'type' => PermissionType::VIEW]);

        $this->assertFalse($this->repository->hasPermission($user, 'test-repo-resource', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsFalseForUnknownResource(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertFalse($this->repository->hasPermission($user, 'does-not-exist', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueAfterDirectGrant(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $permission = Permission::factory()->create(['resource' => 'test-repo-resource-2', 'type' => PermissionType::VIEW]);
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ]);

        $this->assertTrue($this->repository->hasPermission($user, 'test-repo-resource-2', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testGetUserPermissionNamesReturnsAllPermissionsForAdmin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $permission = Permission::factory()->create(['resource' => 'test-repo-admin-resource', 'type' => PermissionType::VIEW]);

        $this->assertContains($permission->name, $this->repository->getUserPermissionNames($admin));
    }

    /**
     * @return void
     */
    public function testGetUserPermissionNamesOnlyReturnsGrantedPermissionsForNonAdmin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $granted = Permission::factory()->create(['resource' => 'test-repo-granted', 'type' => PermissionType::VIEW]);
        Permission::factory()->create(['resource' => 'test-repo-not-granted', 'type' => PermissionType::VIEW]);
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $granted->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ]);

        $names = $this->repository->getUserPermissionNames($user);

        $this->assertContains($granted->name, $names);
        $this->assertNotContains('test-repo-not-granted.view', $names);
    }
}
