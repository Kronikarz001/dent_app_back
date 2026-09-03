<?php

namespace Tests\Unit\Middlewares;

use App\Enums\PermissionType;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for App\Http\Middlewares\PermissionMiddleware — the
 * global resource-permission gate on the api group. Exercised through real
 * routes rather than a bare Request, since its behavior depends on the
 * resolved route name and the registered PermissionRoute mappings.
 */
class PermissionMiddlewareTest extends TestCase
{
    /**
     * @return void
     */
    public function testDeniesAccessWhenUserHasNoGrantForResource(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('patient.index'));

        $response->assertStatus(403);
    }

    /**
     * @return void
     */
    public function testAllowsAccessWhenUserHasGrantForResource(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $permission = Permission::where('resource', 'patient')->where('type', PermissionType::VIEW->value)->firstOrFail();
        PermissionAssignment::create([
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => User::class,
            'assignable_id' => $user->uuid,
        ]);
        Patient::factory()->create();

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('patient.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testAdminBypassesResourcePermissionCheck(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->callApiWithLoggedUser($admin)
            ->getJson(route('patient.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testAlwaysAllowedRouteBypassesResourceCheckEvenWithoutAnyGrant(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('auth.logout'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testUnauthenticatedRequestIsRejectedBeforeReachingResourceCheck(): void
    {
        $response = $this->getJson(route('patient.index'));

        $response->assertStatus(401);
    }
}
