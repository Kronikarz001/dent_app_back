<?php

use App\Models\MessageGroup;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{userUuid}', function ($user, string $userUuid) {
    return $user->uuid === $userUuid;
});

Broadcast::channel('message-group.{groupUuid}', function ($user, string $groupUuid) {
    return MessageGroup::query()
        ->where('uuid', $groupUuid)
        ->whereHas('users', fn ($query) => $query->where('users.uuid', $user->uuid))
        ->exists();
});
