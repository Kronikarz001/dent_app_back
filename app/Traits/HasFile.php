<?php

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Summary of HasFile
 */
trait HasFile
{
    /**
     * @return MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return int
     */
    public function countFiles(): int
    {
        return $this->files()->count();
    }
}
