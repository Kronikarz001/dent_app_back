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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superuser')->default(false)->after('is_admin');
            $table->uuid('job_position_uuid')->nullable()->after('is_superuser');
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('apartment_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();

            $table->foreign('job_position_uuid')
                ->references('uuid')->on('job_positions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['job_position_uuid']);
            $table->dropColumn(['job_position_uuid', 'is_superuser', 'street', 'house_number', 'apartment_number', 'postal_code', 'city']);
        });
    }
};
