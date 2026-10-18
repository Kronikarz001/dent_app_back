<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_medicine', function (Blueprint $table) {
            $table->uuid('prescription_uuid');
            $table->uuid('medicine_uuid');
            $table->unsignedInteger('quantity');
            $table->string('dosage');

            $table->primary(['prescription_uuid', 'medicine_uuid']);

            $table->foreign('prescription_uuid')
                ->references('uuid')->on('prescriptions')
                ->cascadeOnDelete();

            $table->foreign('medicine_uuid')
                ->references('uuid')->on('medicines')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medicine');
    }
};
