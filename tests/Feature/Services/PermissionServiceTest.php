<?php

namespace Tests\Feature\Services;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\RoleGroup;
use App\Models\User;
use App\Models\UserGroup;
use App\Services\PermissionServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Summary of PermissionServiceTest
 */
class PermissionServiceTest extends TestCase
{
    /**
     * @var PermissionServiceInterface|Application|mixed|object
     */
    private PermissionServiceInterface $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PermissionServiceInterface::class);
    }

    /**
     * @param string $grantableType
     * @param string $grantableUuid
     * @param string $assignableType
     * @param string $assignableUuid
     * @param Carbon|null $expiresAt
     * @return PermissionAssignment
     */
    private function grant(string $grantableType, string $grantableUuid, string $assignableType, string $assignableUuid, ?Carbon $expiresAt = null): PermissionAssignment
    {
        return PermissionAssignment::create([
            'grantable_type' => $grantableType,
            'grantable_id' => $grantableUuid,
            'assignable_type' => $assignableType,
            'assignable_id' => $assignableUuid,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsFalseWithoutGrant(): void
    {
        $user = User::factory()->create();
        Permission::factory()->create(['resource' => 'permtest-calendar', 'type' => PermissionType::VIEW->value]);

        $this->assertFalse($this->service->hasPermission($user, 'permtest-calendar', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsFalseForUnknownResource(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->service->hasPermission($user, 'unknown-resource', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForDirectUserGrant(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create(['resource' => 'permtest-calendar', 'type' => PermissionType::VIEW->value]);

        $this->grant(Permission::class, $permission->uuid, User::class, $user->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-calendar', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForGrantViaPermissionGroup(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create(['resource' => 'permtest-calendar', 'type' => PermissionType::EDIT->value]);
        $group = PermissionGroup::factory()->create();
        $group->permissions()->attach($permission->uuid);

        $this->grant(PermissionGroup::class, $group->uuid, User::class, $user->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-calendar', PermissionType::EDIT));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForGrantViaUserGroup(): void
    {
        $user = User::factory()->create();
        $userGroup = UserGroup::factory()->create();
        $userGroup->users()->attach($user->uuid);
        $permission = Permission::factory()->create(['resource' => 'permtest-patient', 'type' => PermissionType::VIEW->value]);

        $this->grant(Permission::class, $permission->uuid, UserGroup::class, $userGroup->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-patient', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForGrantViaRole(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $role->users()->attach($user->uuid);
        $permission = Permission::factory()->create(['resource' => 'permtest-patient', 'type' => PermissionType::EDIT->value]);

        $this->grant(Permission::class, $permission->uuid, Role::class, $role->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-patient', PermissionType::EDIT));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForGrantViaDirectRoleGroupMembership(): void
    {
        $user = User::factory()->create();
        $roleGroup = RoleGroup::factory()->create();
        $roleGroup->users()->attach($user->uuid);
        $permission = Permission::factory()->create(['resource' => 'permtest-material', 'type' => PermissionType::VIEW->value]);

        $this->grant(Permission::class, $permission->uuid, RoleGroup::class, $roleGroup->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-material', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasPermissionReturnsTrueForGrantViaRoleBelongingToRoleGroup(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $role->users()->attach($user->uuid);
        $roleGroup = RoleGroup::factory()->create();
        $roleGroup->roles()->attach($role->uuid);
        $permission = Permission::factory()->create(['resource' => 'permtest-material', 'type' => PermissionType::EDIT->value]);

        $this->grant(Permission::class, $permission->uuid, RoleGroup::class, $roleGroup->uuid);

        $this->assertTrue($this->service->hasPermission($user, 'permtest-material', PermissionType::EDIT));
    }

    /**
     * @return void
     */
    public function testExpiredGrantDoesNotGrantAccess(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create(['resource' => 'permtest-calendar', 'type' => PermissionType::VIEW->value]);

        $this->grant(Permission::class, $permission->uuid, User::class, $user->uuid, Carbon::now()->subDay());

        $this->assertFalse($this->service->hasPermission($user, 'permtest-calendar', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testFutureExpiryStillGrantsAccess(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create(['resource' => 'permtest-calendar', 'type' => PermissionType::VIEW->value]);

        $this->grant(Permission::class, $permission->uuid, User::class, $user->uuid, Carbon::now()->addDay());

        $this->assertTrue($this->service->hasPermission($user, 'permtest-calendar', PermissionType::VIEW));
    }

    /**
     * @return void
     */
    public function testHasGroupGrantRequiresExactGroupGrant(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();
        $group = PermissionGroup::factory()->create();
        $group->permissions()->attach($permission->uuid);

        $this->assertFalse($this->service->hasGroupGrant($user, $group));

        $this->grant(Permission::class, $permission->uuid, User::class, $user->uuid);
        $this->assertFalse($this->service->hasGroupGrant($user, $group));

        $this->grant(PermissionGroup::class, $group->uuid, User::class, $user->uuid);
        $this->assertTrue($this->service->hasGroupGrant($user, $group));
    }

    /**
     * @return void
     */
    public function testHasPermissionGrantMatchesDirectAndViaGroup(): void
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->assertFalse($this->service->hasPermissionGrant($user, $permission));

        $this->grant(Permission::class, $permission->uuid, User::class, $user->uuid);

        $this->assertTrue($this->service->hasPermissionGrant($user, $permission));
    }
}
