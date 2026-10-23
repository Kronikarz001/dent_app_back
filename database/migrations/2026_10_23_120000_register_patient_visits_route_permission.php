<?php

use Database\Seeders\RegisterPatientVisitsRoutePermissionSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RegisterPatientVisitsRoutePermissionSeeder)->run();
    }
};
