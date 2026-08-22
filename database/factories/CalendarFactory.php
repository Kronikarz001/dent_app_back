<?php

namespace Database\Factories;

use App\Enums\CalendarEventType;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Summary of CalendarFactory
 */
class CalendarFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Calendar::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'type' => $this->faker->randomElement(CalendarEventType::appointmentTypes())->value,
            'date' => Carbon::now()->toDateString(),
            'end_date' => null,
            'start_time' => '09:00',
            'end_time' => '09:30',
            'no_show' => false,
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
