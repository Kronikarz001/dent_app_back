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
        Schema::create('user_group_user', function (Blueprint $table) {
            $table->uuid('user_group_uuid');
            $table->uuid('user_uuid');

            $table->primary(['user_group_uuid', 'user_uuid']);

            $table->foreign('user_group_uuid')
                ->references('uuid')->on('user_groups')
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
        Schema::dropIfExists('user_group_user');
    }
};
