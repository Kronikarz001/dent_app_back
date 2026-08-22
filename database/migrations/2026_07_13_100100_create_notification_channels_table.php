<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->boolean('is_configurable')->default(true);
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        DB::table('notification_channels')->insert([
            [
                'uuid' => Str::uuid(),
                'name' => 'database',
                'display_name' => 'Baza danych',
                'is_configurable' => false,
                'is_internal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'broadcast',
                'display_name' => 'Broadcast',
                'is_configurable' => false,
                'is_internal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'mail',
                'display_name' => 'E-mail',
                'is_configurable' => true,
                'is_internal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => Str::uuid(),
                'name' => 'in-app',
                'display_name' => 'W aplikacji',
                'is_configurable' => true,
                'is_internal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
    }
};
