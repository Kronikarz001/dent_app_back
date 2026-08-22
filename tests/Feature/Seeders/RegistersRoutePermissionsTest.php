<?php

namespace Tests\Feature\Seeders;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\PermissionRoute;
use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;
use Tests\TestCase;

/**
 * Summary of RegistersRoutePermissionsTest
 */
class RegistersRoutePermissionsTest extends TestCase
{
    /**
     * @return void
     */
    public function testRunCreatesViewAndEditPermissionsAndRegistersRoutes(): void
    {
        $seeder = new class extends Seeder
        {
            use RegistersRoutePermissions;

            protected function resourceRoutes(): array
            {
                return [
                    'widget' => ['widget.index', 'widget.store'],
                ];
            }
        };

        $seeder->run();

        $this->assertDatabaseHas('permissions', ['resource' => 'widget', 'type' => PermissionType::VIEW->value]);
        $this->assertDatabaseHas('permissions', ['resource' => 'widget', 'type' => PermissionType::EDIT->value]);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'widget.index', 'resource' => 'widget']);
        $this->assertDatabaseHas('permission_routes', ['route_name' => 'widget.store', 'resource' => 'widget']);
    }

    /**
     * @return void
     */
    public function testRunIsIdempotent(): void
    {
        $seeder = new class extends Seeder
        {
            use RegistersRoutePermissions;

            protected function resourceRoutes(): array
            {
                return [
                    'widget' => ['widget.index'],
                ];
            }
        };

        $seeder->run();
        $seeder->run();

        $this->assertSame(2, Permission::where('resource', 'widget')->count());
        $this->assertSame(1, PermissionRoute::where('resource', 'widget')->count());
    }

    /**
     * @return void
     */
    public function testRunCanReassignRouteToDifferentResource(): void
    {
        $first = new class extends Seeder
        {
            use RegistersRoutePermissions;

            protected function resourceRoutes(): array
            {
                return ['widget' => ['widget.index']];
            }
        };
        $first->run();

        $second = new class extends Seeder
        {
            use RegistersRoutePermissions;

            protected function resourceRoutes(): array
            {
                return ['gadget' => ['widget.index']];
            }
        };
        $second->run();

        $this->assertDatabaseHas('permission_routes', ['route_name' => 'widget.index', 'resource' => 'gadget']);
        $this->assertDatabaseMissing('permission_routes', ['route_name' => 'widget.index', 'resource' => 'widget']);
    }
}
