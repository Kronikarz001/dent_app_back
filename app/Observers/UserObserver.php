<?php

namespace App\Observers;

use App\Models\MessageGroup;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $defaultGroup = MessageGroup::where('is_default', true)->first();

        if ($defaultGroup) {
            $defaultGroup->users()->attach($user->uuid);
        }
    }
}
