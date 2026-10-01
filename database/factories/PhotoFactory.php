<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'extension' => $this->faker->randomElement(['jpg', 'png', 'webp']),
            'filename' => $this->faker->word(),
            'size' => $this->faker->randomNumber(),
            'path' => $this->faker->imageUrl(),
            'mimetype' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/webp']),
            'patient_uuid' => Patient::factory(),
            'user_uuid' => User::factory(),
        ];
    }
}
