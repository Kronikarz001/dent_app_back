<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $uuid
 * @property string $title
 * @property string $content
 * @property Carbon $published_at
 * @property string|null $user_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Announcement extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'title',
        'content',
        'published_at',
        'user_uuid',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
