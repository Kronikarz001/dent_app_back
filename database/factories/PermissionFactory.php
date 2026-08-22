<?php

namespace Database\Factories;

use App\Enums\PermissionType;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Summary of PermissionFactory
 */
class PermissionFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Permission::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'resource' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement(PermissionType::cases())->value,
        ];
    }
}
