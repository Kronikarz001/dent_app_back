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
        Schema::create('calendar_users', function (Blueprint $table) {
            $table->uuid('calendar_uuid');
            $table->uuid('userable_id');
            $table->string('userable_type');
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['calendar_uuid', 'userable_id', 'userable_type'], 'calendar_users_unique_assignment');
            $table->index(['userable_type', 'userable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_users');
    }
};
