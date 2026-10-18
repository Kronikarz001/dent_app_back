<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('patients')
            ->whereNull('gender')
            ->whereNotNull('pesel')
            ->chunkById(500, function ($patients) {
                foreach ($patients as $patient) {
                    $gender = Gender::fromPesel($patient->pesel);

                    if ($gender === null) {
                        continue;
                    }

                    DB::table('patients')
                        ->where('uuid', $patient->uuid)
                        ->update(['gender' => $gender->value]);
                }
            }, 'uuid');
    }

    public function down(): void {}
};
