<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\RegisterPermissionAdminRoutePermissionsSeeder;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Summary of RegisterPermissionAdminRoutePermissionsSeederTest
 */
class RegisterPermissionAdminRoutePermissionsSeederTest extends TestCase
{
    /**
     * @return void
     */
    public function testEveryRegisteredRouteNameIsARealRoute(): void
    {
        $seeder = new RegisterPermissionAdminRoutePermissionsSeeder;
        $seeder->run();

        $knownRouteNames = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->all();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'role.index', 'resource' => 'role']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'user-group.assignUsers', 'resource' => 'user-group']);
        $this->assertDatabaseMissing('permission_routes', ['route_name' => 'role.delegate']);
        $this->assertDatabaseMissing('permission_routes', ['route_name' => 'role-group.delegate']);

        $method = (new ReflectionClass($seeder))->getMethod('resourceRoutes');
        $method->setAccessible(true);

        foreach ($method->invoke($seeder) as $routeNames) {
            foreach ($routeNames as $routeName) {
                $this->assertContains($routeName, $knownRouteNames, "Route [{$routeName}] registered in the seeder no longer exists.");
            }
        }
    }
}
