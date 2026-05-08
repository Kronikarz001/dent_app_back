<?php

namespace App\Models;

use App\Observers\FileObserver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $filename
 * @property string $path
 * @property string $extension
 * @property int $size
 * @property string $mimetype
 * @property string $user_id
 * @property string $fileable_type
 * @property string $fileable_id
 * @property Collection $files
 */
class File extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'filename',
        'path',
        'extension',
        'size',
        'user_id',
        'fileable_type',
        'fileable_id',
        'mimetype',
        'file_uuid',
        'is_latest'
    ];

    /**
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::observe(FileObserver::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
