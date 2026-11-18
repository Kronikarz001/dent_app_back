<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_rules', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuid('user_uuid');
            $table->string('day');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->index(['user_uuid', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_rules');
    }
};
