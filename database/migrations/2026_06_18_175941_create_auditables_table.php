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
        Schema::create('auditables', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuidMorphs('auditable');
            $table->foreignUuid('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->string('type');
            $table->json('change_from')->nullable();
            $table->json('change_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditables');
    }
};
