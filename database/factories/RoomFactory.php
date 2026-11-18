<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Gabinet '.$this->faker->unique()->numberBetween(1, 100000),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
