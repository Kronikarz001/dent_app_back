<?php

use Database\Seeders\DefaultMessageGroupSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new DefaultMessageGroupSeeder)->run();
    }
};
