<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignUuid('message_uuid')->references('uuid')->on('messages')->onDelete('cascade');
            $table->foreignUuid('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->primary(['message_uuid', 'user_uuid']);
            $table->timestamps();
        });
    }
};
