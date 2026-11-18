<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Summary of ScheduleRule
 *
 * @property string $uuid
 * @property string $user_uuid
 * @property DayOfWeek $day
 * @property string $start_time
 * @property string $end_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ScheduleRule extends UuidModel
{
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_uuid',
        'day',
        'start_time',
        'end_time',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => DayOfWeek::class,
        ];
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
