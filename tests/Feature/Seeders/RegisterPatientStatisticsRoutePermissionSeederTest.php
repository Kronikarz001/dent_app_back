<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\RegisterPatientStatisticsRoutePermissionSeeder;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Summary of RegisterPatientStatisticsRoutePermissionSeederTest
 */
class RegisterPatientStatisticsRoutePermissionSeederTest extends TestCase
{
    /**
     * @return void
     */
    public function testEveryRegisteredRouteNameIsARealRoute(): void
    {
        $seeder = new RegisterPatientStatisticsRoutePermissionSeeder;
        $seeder->run();

        $knownRouteNames = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->all();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'patient.statistics', 'resource' => 'patient']);

        $method = (new ReflectionClass($seeder))->getMethod('resourceRoutes');
        $method->setAccessible(true);

        foreach ($method->invoke($seeder) as $routeNames) {
            foreach ($routeNames as $routeName) {
                $this->assertContains($routeName, $knownRouteNames, "Route [{$routeName}] registered in the seeder no longer exists.");
            }
        }
    }
}
