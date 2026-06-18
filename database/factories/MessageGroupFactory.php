<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MessageGroupFactory extends Factory
{
    protected $model = MessageGroup::class;

    public function definition(): array
    {
        return [
            'message_uuid' => Message::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
