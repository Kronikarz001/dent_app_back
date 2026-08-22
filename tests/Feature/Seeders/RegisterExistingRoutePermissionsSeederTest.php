<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\RegisterExistingRoutePermissionsSeeder;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Summary of RegisterExistingRoutePermissionsSeederTest
 */
class RegisterExistingRoutePermissionsSeederTest extends TestCase
{
    /**
     * @return void
     */
    public function testEveryRegisteredRouteNameIsARealRoute(): void
    {
        $seeder = new RegisterExistingRoutePermissionsSeeder;
        $seeder->run();

        $knownRouteNames = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->all();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'calendar.index', 'resource' => 'calendar']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'user.destroy', 'resource' => 'user']);
        $this->assertDatabaseMissing('permission_routes', ['route_name' => 'auth.login']);
        $this->assertDatabaseMissing('permission_routes', ['route_name' => 'auth.logout']);

        $method = (new ReflectionClass($seeder))->getMethod('resourceRoutes');
        $method->setAccessible(true);

        foreach ($method->invoke($seeder) as $routeNames) {
            foreach ($routeNames as $routeName) {
                $this->assertContains($routeName, $knownRouteNames, "Route [{$routeName}] registered in the seeder no longer exists.");
            }
        }
    }
}
