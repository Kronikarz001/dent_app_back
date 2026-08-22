<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $user_uuid
 * @property string $notification_type_uuid
 * @property string $notification_channel_uuid
 * @property bool $enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class NotificationPreference extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'user_uuid',
        'notification_type_uuid',
        'notification_channel_uuid',
        'enabled',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'notification_channel_uuid');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_uuid');
    }
}
