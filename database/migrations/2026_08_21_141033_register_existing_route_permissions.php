<?php

use Database\Seeders\RegisterExistingRoutePermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RegisterExistingRoutePermissionsSeeder)->run();
    }
};
