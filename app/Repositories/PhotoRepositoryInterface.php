<?php

namespace App\Repositories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhotoRepositoryInterface
 */
interface PhotoRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param array $data
     * @return Photo
     */
    public function create(array $data): Photo;

    /**
     * @param Model|Photo $model
     * @param array $data
     * @return Photo
     */
    public function update(Model|Photo $model, array $data): Photo;

    /**
     * @param Model|Photo $model
     * @return bool
     */
    public function delete(Model|Photo $model): bool;
}
