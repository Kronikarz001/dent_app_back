<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidFactory;

/**
 * @extends Factory<User>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * Define the model's default state
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phoneable_type' => User::class,
            'phoneable_id' => User::factory()->create()->uuid,
            'number' => fake()->unique()->numerify('##########'),
        ];
    }
}
