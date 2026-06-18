<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Summary of Message
 *
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
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_uuid',
        'recipient_user_uuid',
        'message_group_uuid',
        'message',
    ];

    /**
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(MessageGroup::class, 'message_group_uuid', 'uuid');
    }
}
