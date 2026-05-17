<?php

namespace App\Models;

/**
 * @property string $uuid
 * @property string|null $avatar_path
 */
class UserAvatar extends BasicUser
{
    protected $fillable = ['avatar_path'];
}
