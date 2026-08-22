<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'published_at' => $this->faker->unique()->date(),
            'user_uuid' => null,
        ];
    }
}
