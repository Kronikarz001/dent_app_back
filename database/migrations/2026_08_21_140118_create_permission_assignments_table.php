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
        Schema::create('permission_assignments', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->uuidMorphs('grantable');
            $table->uuidMorphs('assignable');
            $table->timestamp('expires_at')->nullable();
            $table->uuid('granted_by')->nullable();
            $table->timestamps();

            $table->foreign('granted_by')
                ->references('uuid')->on('users')
                ->nullOnDelete();

            $table->unique(
                ['grantable_type', 'grantable_id', 'assignable_type', 'assignable_id'],
                'permission_assignments_unique_grant'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_assignments');
    }
};
