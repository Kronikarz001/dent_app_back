<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterPatientStatisticsRoutePermissionSeeder
 */
class RegisterPatientStatisticsRoutePermissionSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'patient' => [
                'patient.statistics',
            ],
        ];
    }
}
