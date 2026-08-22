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
        Schema::create('department_user', function (Blueprint $table) {
            $table->uuid('department_uuid');
            $table->uuid('user_uuid');
            $table->boolean('is_manager')->default(false);

            $table->primary(['department_uuid', 'user_uuid']);

            $table->foreign('department_uuid')
                ->references('uuid')->on('departments')
                ->cascadeOnDelete();

            $table->foreign('user_uuid')
                ->references('uuid')->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_user');
    }
};
