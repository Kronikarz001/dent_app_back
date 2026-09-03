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
        Schema::create('calendars_dental_examinations', function (Blueprint $table) {
            $table->uuid('calendar_uuid');
            $table->uuid('dental_examination_uuid');
            $table->foreign('calendar_uuid')->references('uuid')->on('calendars')->cascadeOnDelete();
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['calendar_uuid', 'dental_examination_uuid'], 'calendars_dental_examinations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendars_dental_examinations');
    }
};
