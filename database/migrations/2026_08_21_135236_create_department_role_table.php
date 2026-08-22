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
        Schema::create('department_role', function (Blueprint $table) {
            $table->uuid('department_uuid');
            $table->uuid('role_uuid');

            $table->primary(['department_uuid', 'role_uuid']);

            $table->foreign('department_uuid')
                ->references('uuid')->on('departments')
                ->cascadeOnDelete();

            $table->foreign('role_uuid')
                ->references('uuid')->on('roles')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_role');
    }
};
