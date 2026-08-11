<?php

namespace Database\Factories;

use App\Enums\CalendarEventType;
use App\Models\EmployeeSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Summary of EmployeeScheduleFactory
 */
class EmployeeScheduleFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = EmployeeSchedule::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'type' => $this->faker->randomElement(CalendarEventType::employeeTypes())->value,
            'date' => Carbon::now()->toDateString(),
            'end_date' => null,
            'start_time' => '08:00',
            'end_time' => '16:00',
            'no_show' => false,
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
