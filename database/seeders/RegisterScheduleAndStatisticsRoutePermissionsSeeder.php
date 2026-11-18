<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterScheduleAndStatisticsRoutePermissionsSeeder
 */
class RegisterScheduleAndStatisticsRoutePermissionsSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'calendar' => [
                'calendar.today',
            ],
            'own-schedule' => [
                'employee-schedule.my',
                'schedule-rule.index',
                'schedule-rule.store',
                'schedule-rule.show',
                'schedule-rule.update',
                'schedule-rule.destroy',
            ],
            'room' => [
                'room.index',
                'room.store',
                'room.selectList',
                'room.show',
                'room.update',
                'room.destroy',
            ],
            'statistics' => [
                'statistics.users',
                'statistics.summary',
                'statistics.distribution',
                'statistics.daily',
            ],
            'user' => [
                'user.workingToday',
            ],
        ];
    }
}
