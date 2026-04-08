<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Summary of PhoneNumber
 * @property string $uuid
 * @property string $phoneable_type
 * @property string $phoneable_uuid
 * @property string $number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PhoneNumber extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'phoneable_type',
        'phoneable_uuid',
        'number',
    ];

    /**
     * @return MorphTo
     */
    public function phoneNumberable(): MorphTo
    {
        return $this->morphTo();
    }
}
