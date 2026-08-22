<?php

use Database\Seeders\NotificationGroupSeeder;
use Database\Seeders\NotificationTypeSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new NotificationTypeSeeder)->run();
        (new NotificationGroupSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
