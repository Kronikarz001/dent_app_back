<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterPermissionGroupUsersRoutePermissionSeeder
 */
class RegisterPermissionGroupUsersRoutePermissionSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'permission-group' => [
                'permission-group.assignUsers',
            ],
        ];
    }
}
