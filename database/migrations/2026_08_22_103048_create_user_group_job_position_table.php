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
        Schema::create('user_group_job_position', function (Blueprint $table) {
            $table->uuid('user_group_uuid');
            $table->uuid('job_position_uuid');

            $table->primary(['user_group_uuid', 'job_position_uuid']);

            $table->foreign('user_group_uuid')
                ->references('uuid')->on('user_groups')
                ->cascadeOnDelete();

            $table->foreign('job_position_uuid')
                ->references('uuid')->on('job_positions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_group_job_position');
    }
};
