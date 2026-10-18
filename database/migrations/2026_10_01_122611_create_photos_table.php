<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('filename');
            $table->string('path');
            $table->string('extension');
            $table->string('size');
            $table->string('mimetype');
            $table->uuid('patient_uuid');
            $table->uuid('user_uuid');
            $table->timestamps();

            $table->foreign('patient_uuid')->references('uuid')->on('patients')->cascadeOnDelete();
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
