<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_group_users', function (Blueprint $table) {
            $table->foreignUuid('message_group_uuid')->references('uuid')->on('message_groups')->onDelete('cascade');
            $table->foreignUuid('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->primary(['message_group_uuid', 'user_uuid']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_group_users');
    }
};
