<?php

namespace Database\Factories;

use App\Enums\ReimbursementLevel;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'ean_gtin' => $this->faker->unique()->ean13(),
            'reimbursement_level' => $this->faker->randomElement(ReimbursementLevel::cases()),
        ];
    }
}
