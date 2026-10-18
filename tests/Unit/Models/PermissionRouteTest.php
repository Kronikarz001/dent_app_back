<?php

namespace Tests\Unit\Models;

use App\Models\PermissionRoute;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PermissionRouteTest
 */
final class PermissionRouteTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRouteNameAndResourceAreMassAssignable(): void
    {
        $route = PermissionRoute::create([
            'route_name' => 'test.route',
            'resource' => 'test',
        ]);

        $this->assertDatabaseHas('permission_routes', [
            'uuid' => $route->uuid,
            'route_name' => 'test.route',
            'resource' => 'test',
        ]);
    }
}
