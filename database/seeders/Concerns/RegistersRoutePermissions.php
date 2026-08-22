<?php

namespace Database\Seeders\Concerns;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionRoute;

/**
 * Summary of RegistersRoutePermissions
 */
trait RegistersRoutePermissions
{
    /**
     * @return void
     */
    public function run(): void
    {
        foreach ($this->resourceRoutes() as $resource => $routeNames) {
            foreach (PermissionType::cases() as $type) {
                Permission::firstOrCreate([
                    'resource' => $resource,
                    'type' => $type->value,
                ]);
            }

            foreach ($routeNames as $routeName) {
                PermissionRoute::updateOrCreate(
                    ['route_name' => $routeName],
                    ['resource' => $resource]
                );
            }
        }
    }

    /**
     * @return array<string, string[]>
     */
    abstract protected function resourceRoutes(): array;
}
