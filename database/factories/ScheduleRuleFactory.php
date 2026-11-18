<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\ScheduleRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleRule>
 */
class ScheduleRuleFactory extends Factory
{
    protected $model = ScheduleRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_uuid' => User::factory(),
            'day' => $this->faker->randomElement(DayOfWeek::cases()),
            'start_time' => '08:00',
            'end_time' => '16:00',
        ];
    }
}
