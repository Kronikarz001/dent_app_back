<?php

namespace Tests\Feature\Controllers;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of DepartmentControllerTest
 */
class DepartmentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        Department::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('department.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('department.store'), ['name' => 'Zarząd']);

        $response->assertCreated();
        $this->assertDatabaseHas('departments', ['name' => 'Zarząd']);
    }

    /**
     * @return void
     */
    public function testAssignRolesSyncsRoles(): void
    {
        $department = Department::factory()->create();
        $role = Role::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('department.assignRoles', ['department' => $department->uuid]), ['roles' => [$role->uuid]])
            ->assertNoContent();

        $this->assertDatabaseHas('department_role', ['department_uuid' => $department->uuid, 'role_uuid' => $role->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignJobPositionsSyncsJobPositions(): void
    {
        $department = Department::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('department.assignJobPositions', ['department' => $department->uuid]), ['job_positions' => [$jobPosition->uuid]])
            ->assertNoContent();

        $this->assertDatabaseHas('department_job_position', ['department_uuid' => $department->uuid, 'job_position_uuid' => $jobPosition->uuid]);
    }

    /**
     * @return void
     */
    public function testAssignUsersSetsManagerFlag(): void
    {
        $department = Department::factory()->create();
        $manager = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('department.assignUsers', ['department' => $department->uuid]), [
                'users' => [['uuid' => $manager->uuid, 'is_manager' => true]],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('department_user', ['department_uuid' => $department->uuid, 'user_uuid' => $manager->uuid, 'is_manager' => true]);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsForbiddenWhenActingUserLacksGrant(): void
    {
        $actingUser = User::factory()->create(['is_admin' => false]);
        $department = Department::factory()->create();

        $ownResourcePermission = Permission::where('resource', 'department')->where('type', 'edit')->firstOrFail();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $ownResourcePermission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $actingUser->uuid,
        ]);

        $unownedPermission = Permission::where('resource', 'user')->where('type', 'edit')->firstOrFail();

        $response = $this->callApiWithLoggedUser($actingUser)
            ->patchJson(route('department.assignPermissions', ['department' => $department->uuid]), [
                'permissions' => [$unownedPermission->uuid],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $unownedPermission->uuid,
            'assignable_type' => Department::class,
            'assignable_id' => $department->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDelegateAllowsManagerToShareOwnPermissionWithDepartmentMemberViaRole(): void
    {
        $department = Department::factory()->create();
        $manager = User::factory()->create();
        $department->users()->attach($manager->uuid, ['is_manager' => true]);

        $role = Role::factory()->create();
        $department->roles()->attach($role->uuid);
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
            ->postJson(route('department.delegate', ['department' => $department->uuid]), [
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
    public function testDelegateAllowsManagerOfAttachedRoleToShareOwnPermissionWithDepartmentMember(): void
    {
        $department = Department::factory()->create();
        $role = Role::factory()->create();
        $department->roles()->attach($role->uuid);
        $manager = User::factory()->create();
        $role->users()->attach($manager->uuid, ['is_manager' => true]);
        $member = User::factory()->create();
        $department->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $manager->uuid,
        ]);

        $this->callApiWithLoggedUser($manager)
            ->postJson(route('department.delegate', ['department' => $department->uuid]), [
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
        $department = Department::factory()->create();
        $notManager = User::factory()->create();
        $member = User::factory()->create();
        $department->users()->attach($notManager->uuid, ['is_manager' => false]);
        $department->users()->attach($member->uuid, ['is_manager' => false]);

        $permission = Permission::factory()->create();

        $response = $this->callApiWithLoggedUser($notManager)
            ->postJson(route('department.delegate', ['department' => $department->uuid]), [
                'user_uuid' => $member->uuid,
                'permission_uuid' => $permission->uuid,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testCreateRoleAllowsManagerToCreateRoleWithinDepartmentPermissions(): void
    {
        $department = Department::factory()->create();
        $manager = User::factory()->create();
        $department->users()->attach($manager->uuid, ['is_manager' => true]);

        $permission = Permission::factory()->create();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => Department::class,
            'assignable_id' => $department->uuid,
        ]);

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('department.createRole', ['department' => $department->uuid]), [
                'name' => 'Recepcjonistka',
                'permissions' => [$permission->uuid],
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('roles', ['name' => 'Recepcjonistka']);
        $role = Role::query()->where('name', 'Recepcjonistka')->firstOrFail();
        $this->assertDatabaseHas('department_role', ['department_uuid' => $department->uuid, 'role_uuid' => $role->uuid]);
        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => Role::class,
            'assignable_id' => $role->uuid,
            'granted_by' => $manager->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateRoleForbiddenForNonManager(): void
    {
        $department = Department::factory()->create();
        $notManager = User::factory()->create();
        $department->users()->attach($notManager->uuid, ['is_manager' => false]);

        $response = $this->callApiWithLoggedUser($notManager)
            ->postJson(route('department.createRole', ['department' => $department->uuid]), [
                'name' => 'Recepcjonistka',
            ]);

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testCreateRoleForbiddenWhenPermissionExceedsDepartmentGrants(): void
    {
        $department = Department::factory()->create();
        $manager = User::factory()->create();
        $department->users()->attach($manager->uuid, ['is_manager' => true]);

        $permission = Permission::factory()->create();

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('department.createRole', ['department' => $department->uuid]), [
                'name' => 'Recepcjonistka',
                'permissions' => [$permission->uuid],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('roles', ['name' => 'Recepcjonistka']);
    }

    /**
     * @return void
     */
    public function testCreateRoleForbiddenWhenPermissionGroupExceedsDepartmentGrants(): void
    {
        $department = Department::factory()->create();
        $manager = User::factory()->create();
        $department->users()->attach($manager->uuid, ['is_manager' => true]);

        $group = PermissionGroup::factory()->create();

        $response = $this->callApiWithLoggedUser($manager)
            ->postJson(route('department.createRole', ['department' => $department->uuid]), [
                'name' => 'Recepcjonistka',
                'permission_groups' => [$group->uuid],
            ]);

        $response->assertStatus(403);
    }
}
