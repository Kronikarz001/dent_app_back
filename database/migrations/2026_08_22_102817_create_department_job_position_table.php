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
        Schema::create('department_job_position', function (Blueprint $table) {
            $table->uuid('department_uuid');
            $table->uuid('job_position_uuid');

            $table->primary(['department_uuid', 'job_position_uuid']);

            $table->foreign('department_uuid')
                ->references('uuid')->on('departments')
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
        Schema::dropIfExists('department_job_position');
    }
};
