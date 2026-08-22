<?php

namespace Database\Factories;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Summary of PermissionGroupFactory
 */
class PermissionGroupFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = PermissionGroup::class;

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
