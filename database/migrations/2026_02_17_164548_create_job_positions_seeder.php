<?php

use Database\Seeders\JobPositionSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * @return Migration
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up(): void
    {
        (new JobPositionSeeder())->run();
    }
};
