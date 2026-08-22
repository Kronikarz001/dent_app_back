<?php

namespace Database\Factories;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Summary of UserGroupFactory
 */
class UserGroupFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = UserGroup::class;

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
