<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Summary of MessageGroup
 *
 * @property string $uuid
 * @property string $message_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MessageGroup extends UuidModel
{
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'message_uuid',
    ];

    /**
     * @return BelongsTo
     */
    public function rootMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_uuid', 'uuid');
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'message_group_uuid', 'uuid');
    }
}
