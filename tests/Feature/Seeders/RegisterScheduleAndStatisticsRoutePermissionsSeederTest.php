<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\RegisterScheduleAndStatisticsRoutePermissionsSeeder;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Summary of RegisterScheduleAndStatisticsRoutePermissionsSeederTest
 */
class RegisterScheduleAndStatisticsRoutePermissionsSeederTest extends TestCase
{
    /**
     * @return void
     */
    public function testEveryRegisteredRouteNameIsARealRoute(): void
    {
        $seeder = new RegisterScheduleAndStatisticsRoutePermissionsSeeder;
        $seeder->run();

        $knownRouteNames = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->all();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'employee-schedule.my', 'resource' => 'own-schedule']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'statistics.users', 'resource' => 'statistics']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'room.index', 'resource' => 'room']);

        $method = (new ReflectionClass($seeder))->getMethod('resourceRoutes');
        $method->setAccessible(true);

        foreach ($method->invoke($seeder) as $routeNames) {
            foreach ($routeNames as $routeName) {
                $this->assertContains($routeName, $knownRouteNames, "Route [{$routeName}] registered in the seeder no longer exists.");
            }
        }
    }
}
