<?php

namespace Database\Factories;

use App\Models\RoleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Summary of RoleGroupFactory
 */
class RoleGroupFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = RoleGroup::class;

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
