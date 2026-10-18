<?php

use Database\Seeders\RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RegisterPhotoMedicinePrescriptionRoutePermissionsSeeder)->run();
    }
};
