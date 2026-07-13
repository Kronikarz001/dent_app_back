<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('code')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->uuid('notification_group_uuid')->nullable();
            $table->timestamps();

            $table->foreign('notification_group_uuid')
                ->references('uuid')
                ->on('notification_groups')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
