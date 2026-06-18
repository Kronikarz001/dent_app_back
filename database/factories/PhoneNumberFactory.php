<?php

namespace Database\Factories;

use App\Enums\PhoneNumberType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'phoneable_type' => User::class,
            'phoneable_uuid' => User::factory()->create()->uuid,
            'number' => fake()->unique()->numerify('##########'),
            'type' => fake()->randomElement(PhoneNumberType::cases())->value,
        ];
    }
}
