<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 */
class Message extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'user_uuid',
        'message',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
