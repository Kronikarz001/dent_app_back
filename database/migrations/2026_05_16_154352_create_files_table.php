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
        Schema::create('files', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('filename');
            $table->string('path');
            $table->string('extension');
            $table->string('size');
            $table->string('mimetype');
            $table->uuidMorphs('fileable');
            $table->uuid('user_uuid');
            $table->uuid('file_uuid')->nullable();
            $table->boolean('is_latest')->default(true);
            $table->timestamps();

            $table->foreign('user_uuid')
                ->references('uuid')
                ->on('users')
                ->cascadeOnDelete();

        });
        Schema::table('files', function (Blueprint $table) {
            $table->foreign('file_uuid')
                ->references('uuid')
                ->on('files')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
