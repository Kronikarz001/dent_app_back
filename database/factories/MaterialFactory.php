<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'short_description' => $this->faker->word(),
            'price' => $this->faker->numberBetween(10, 300),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
