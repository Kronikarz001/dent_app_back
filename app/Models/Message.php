<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string|null $user_uuid
 * @property string|null $recipient_user_uuid
 * @property string|null $message_group_uuid
 * @property string $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Message extends UuidModel
{
    use Auditable, HasFile;

    protected $fillable = [
        'user_uuid',
        'recipient_user_uuid',
        'message_group_uuid',
        'message',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_uuid', 'uuid');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MessageGroup::class, 'message_group_uuid', 'uuid');
    }
}
