<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $name
 * @property string $display_name
 * @property bool $is_configurable
 * @property bool $is_internal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class NotificationChannel extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'display_name',
        'is_configurable',
        'is_internal',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_configurable' => 'boolean',
        'is_internal' => 'boolean',
    ];

    public function preferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'notification_channel_uuid');
    }
}
