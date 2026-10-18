<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

/**
 * Summary of RegisterPhotoMedicinePrescriptionRoutePermissionsSeederTest
 */
class RegisterPhotoMedicinePrescriptionRoutePermissionsSeederTest extends TestCase
{
    /**
     * @return void
     */
    public function testEveryRegisteredRouteNameIsARealRoute(): void
    {
        $seeder = new RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder;
        $seeder->run();

        $knownRouteNames = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->all();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'patientphoto.store', 'resource' => 'patientphoto']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'patient.romexisSyncList', 'resource' => 'patientphoto']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'medicine.destroy', 'resource' => 'medicine']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'prescription.issue', 'resource' => 'prescription']);

        $method = (new ReflectionClass($seeder))->getMethod('resourceRoutes');
        $method->setAccessible(true);

        foreach ($method->invoke($seeder) as $routeNames) {
            foreach ($routeNames as $routeName) {
                $this->assertContains($routeName, $knownRouteNames, "Route [{$routeName}] registered in the seeder no longer exists.");
            }
        }
    }
}
