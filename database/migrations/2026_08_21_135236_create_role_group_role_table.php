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
        Schema::create('role_group_role', function (Blueprint $table) {
            $table->uuid('role_group_uuid');
            $table->uuid('role_uuid');

            $table->primary(['role_group_uuid', 'role_uuid']);

            $table->foreign('role_group_uuid')
                ->references('uuid')->on('role_groups')
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
        Schema::dropIfExists('role_group_role');
    }
};
