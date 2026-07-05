<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_groups', function (Blueprint $table) {
            $table->dropForeign(['message_uuid']);
            $table->dropColumn('message_uuid');
            $table->string('name');
            $table->foreignUuid('creator_uuid')->nullable()->references('uuid')->on('users')->onDelete('set null');
            $table->boolean('is_default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('message_groups', function (Blueprint $table) {
            $table->dropForeign(['creator_uuid']);
            $table->dropColumn(['name', 'creator_uuid', 'is_default']);
            $table->foreignUuid('message_uuid')->references('uuid')->on('messages');
        });
    }
};
