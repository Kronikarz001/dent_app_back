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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('gender')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('doctor_uuid')->nullable();

            $table->foreign('doctor_uuid')
                ->references('uuid')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['doctor_uuid']);
            $table->dropColumn(['street', 'house_number', 'apartment_number', 'postal_code', 'city', 'gender', 'notes', 'doctor_uuid']);
        });
    }
};
