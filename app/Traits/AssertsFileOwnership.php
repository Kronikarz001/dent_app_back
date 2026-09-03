<?php

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Summary of AssertsFileOwnership
 */
trait AssertsFileOwnership
{
    /**
     * Guards *FileController endpoints against IDOR: a route-bound File must
     * actually belong to the route-bound parent resource, not just exist.
     *
     * @param File $file
     * @param Model $parent
     * @return void
     *
     * @throws ModelNotFoundException
     */
    protected function assertFileBelongsTo(File $file, Model $parent): void
    {
        if ($file->fileable_type !== $parent->getMorphClass() || $file->fileable_id !== $parent->uuid) {
            throw (new ModelNotFoundException)->setModel(File::class, [$file->uuid]);
        }
    }
}
