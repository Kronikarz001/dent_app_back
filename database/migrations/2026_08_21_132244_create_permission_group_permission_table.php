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
        Schema::create('permission_group_permission', function (Blueprint $table) {
            $table->uuid('permission_group_uuid');
            $table->uuid('permission_uuid');

            $table->primary(['permission_group_uuid', 'permission_uuid']);

            $table->foreign('permission_group_uuid')
                ->references('uuid')->on('permission_groups')
                ->cascadeOnDelete();

            $table->foreign('permission_uuid')
                ->references('uuid')->on('permissions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_group_permission');
    }
};
