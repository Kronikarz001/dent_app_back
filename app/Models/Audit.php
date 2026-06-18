<?php

namespace App\Models;

use App\Enums\AuditableEventType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Summary of Audit
 *
 * @property string $uuid
 * @property string $auditable_type
 * @property string $auditable_id
 * @property string $user_uuid
 * @property AuditableEventType $type
 * @property array|null $change_from
 * @property array|null $change_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Audit extends UuidModel
{
    /**
     * @var string
     */
    protected $table = 'auditables';

    /**
     * @var string[]
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_uuid',
        'type',
        'change_from',
        'change_to',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AuditableEventType::class,
            'change_from' => 'array',
            'change_to' => 'array',
        ];
    }

    /**
     * @return MorphTo
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
