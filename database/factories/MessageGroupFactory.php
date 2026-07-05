<?php

namespace Database\Factories;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MessageGroupFactory extends Factory
{
    protected $model = MessageGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'creator_uuid' => null,
            'is_default' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
