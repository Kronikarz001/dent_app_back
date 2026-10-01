<?php

namespace App\Models;

use App\Observers\PhotoObserver;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $filename
 * @property string $path
 * @property string $extension
 * @property int $size
 * @property string $mimetype
 * @property string $patient_uuid
 * @property string $user_uuid
 */
#[ObservedBy(PhotoObserver::class)]
class Photo extends UuidModel
{
    use Auditable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'filename',
        'path',
        'extension',
        'size',
        'mimetype',
        'patient_uuid',
        'user_uuid',
    ];

    /**
     * @return BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
