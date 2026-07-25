<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->string('name');
            $table->string('regon');
            $table->string('nip');
            $table->string('address');
            $table->string('province');
            $table->string('district');
            $table->string('municipality');
            $table->string('business_form')->nullable();
            $table->string('type_of_business')->nullable();
            $table->string('form_of_ownership')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
