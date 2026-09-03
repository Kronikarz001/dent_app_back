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
        Schema::create('message_groups', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('message_uuid')->references('uuid')->on('messages');
            $table->timestamps();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('message_group_uuid')->references('uuid')->on('message_groups')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['message_group_uuid']);
        });

        Schema::dropIfExists('message_groups');
    }
};
