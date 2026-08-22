<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $code
 * @property string $display_name
 * @property string|null $description
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class NotificationGroup extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'display_name',
        'description',
        'order',
    ];

    public function notificationTypes(): HasMany
    {
        return $this->hasMany(NotificationType::class, 'notification_group_uuid');
    }
}
