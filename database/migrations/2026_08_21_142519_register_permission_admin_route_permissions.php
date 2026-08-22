<?php

use Database\Seeders\RegisterPermissionAdminRoutePermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RegisterPermissionAdminRoutePermissionsSeeder)->run();
    }
};
