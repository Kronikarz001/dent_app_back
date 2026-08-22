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
        Schema::create('role_user', function (Blueprint $table) {
            $table->uuid('role_uuid');
            $table->uuid('user_uuid');
            $table->boolean('is_manager')->default(false);

            $table->primary(['role_uuid', 'user_uuid']);

            $table->foreign('role_uuid')
                ->references('uuid')->on('roles')
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
        Schema::dropIfExists('role_user');
    }
};
