<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('user_uuid');
            $table->uuid('notification_type_uuid');
            $table->uuid('notification_channel_uuid');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(
                ['user_uuid', 'notification_type_uuid', 'notification_channel_uuid'],
                'user_type_channel_unique'
            );

            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('notification_type_uuid')->references('uuid')->on('notification_types')->onDelete('cascade');
            $table->foreign('notification_channel_uuid')->references('uuid')->on('notification_channels')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
