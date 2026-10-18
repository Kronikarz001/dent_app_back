<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionAssignmentTest
 */
final class PermissionAssignmentTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGrantableAndAssignableResolveToRealModels(): void
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();

        $assignment = PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ]);

        $this->assertTrue($assignment->grantable->is($permission));
        $this->assertTrue($assignment->assignable->is($user));
    }

    /**
     * @return void
     */
    public function testGrantedByReturnsGrantingUser(): void
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();
        $grantedBy = User::factory()->create();

        $assignment = PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
            'granted_by' => $grantedBy->uuid,
        ]);

        $this->assertTrue($assignment->grantedBy->is($grantedBy));
    }

    /**
     * @return void
     */
    public function testScopeNotExpiredExcludesExpiredAssignments(): void
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();

        $expired = PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse(PermissionAssignment::notExpired()->get()->contains($expired));
    }

    /**
     * @return void
     */
    public function testScopeNotExpiredIncludesAssignmentsWithoutExpiry(): void
    {
        $permission = Permission::factory()->create();
        $user = User::factory()->create();

        $assignment = PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ]);

        $this->assertTrue(PermissionAssignment::notExpired()->get()->contains($assignment));
    }
}
