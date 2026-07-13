<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('key')->nullable();
            $table->string('value', 512)->nullable();
            $table->json('additional')->nullable();
            $table->string('type', 512);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
    }
};
