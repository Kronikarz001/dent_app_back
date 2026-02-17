<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_job_positions', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->uuid('job_position_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users');
            $table->foreign('job_position_uuid')->references('uuid')->on('job_positions');
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_job_positions');
    }
};
