<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $code
 * @property string $display_name
 * @property string|null $description
 * @property string|null $notification_group_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class NotificationType extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'display_name',
        'description',
        'notification_group_uuid',
    ];

    public function preferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'notification_type_uuid');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_type_uuid');
    }

    public function notificationGroup(): BelongsTo
    {
        return $this->belongsTo(NotificationGroup::class, 'notification_group_uuid');
    }
}
