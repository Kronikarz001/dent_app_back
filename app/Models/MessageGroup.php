<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $name
 * @property string|null $creator_uuid
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MessageGroup extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'name',
        'creator_uuid',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_uuid', 'uuid');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'message_group_users',
            'message_group_uuid',
            'user_uuid',
            'uuid',
            'uuid'
        )->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'message_group_uuid', 'uuid');
    }
}
