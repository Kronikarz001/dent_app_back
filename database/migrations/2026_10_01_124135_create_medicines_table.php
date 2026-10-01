<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('ean_gtin')->nullable()->unique();
            $table->string('reimbursement_level');
            $table->string('external_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
