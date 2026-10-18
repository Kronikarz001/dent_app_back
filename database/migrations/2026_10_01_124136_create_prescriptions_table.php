<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('status');
            $table->uuid('patient_uuid');
            $table->uuid('doctor_uuid');
            $table->string('external_reference')->nullable();
            $table->timestamps();

            $table->foreign('patient_uuid')->references('uuid')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_uuid')->references('uuid')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
