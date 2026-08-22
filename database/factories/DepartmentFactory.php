<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Summary of DepartmentFactory
 */
class DepartmentFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Department::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
        ];
    }
}
