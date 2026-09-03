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
        Schema::create('dental_examinations_materials', function (Blueprint $table) {
            $table->uuid('dental_examination_uuid');
            $table->uuid('material_uuid');
            $table->foreign('dental_examination_uuid')->references('uuid')->on('dental_examinations')->cascadeOnDelete();
            $table->foreign('material_uuid')->references('uuid')->on('materials')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['dental_examination_uuid', 'material_uuid'], 'dental_examinations_materials_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_examinations_materials');
    }
};
