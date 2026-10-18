<?php

namespace Database\Factories;

use App\Enums\PrescriptionStatus;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => $this->faker->unique()->numerify('####'),
            'status' => $this->faker->randomElement(PrescriptionStatus::cases()),
            'patient_uuid' => Patient::factory(),
            'doctor_uuid' => User::factory(),
        ];
    }
}
