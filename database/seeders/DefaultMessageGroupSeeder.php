<?php

namespace Database\Seeders;

use App\Models\MessageGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultMessageGroupSeeder extends Seeder
{
    public function run(): void
    {
        $group = MessageGroup::create([
            'name' => 'Wszyscy',
            'is_default' => true,
        ]);

        $userUuids = User::pluck('uuid');
        $group->users()->attach($userUuids);
    }
}
