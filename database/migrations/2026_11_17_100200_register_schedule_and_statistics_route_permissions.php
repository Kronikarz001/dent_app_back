<?php

use Database\Seeders\RegisterScheduleAndStatisticsRoutePermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RegisterScheduleAndStatisticsRoutePermissionsSeeder)->run();
    }
};
