<?php

namespace Database\Factories;

use App\Models\DentalExamination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<DentalExamination>
 */
class DentalExaminationFactory extends Factory
{
    protected $model = DentalExamination::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(50, 500),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
