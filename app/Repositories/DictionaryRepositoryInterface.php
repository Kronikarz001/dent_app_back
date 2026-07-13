<?php

namespace App\Repositories;

use App\Models\Dictionary;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DictionaryRepositoryInterface
 */
interface DictionaryRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param array $data
     * @return Dictionary
     */
    public function create(array $data): Dictionary;

    /**
     * @param Dictionary|Model $model
     * @param array $data
     * @return Dictionary
     */
    public function update(Dictionary|Model $model, array $data): Dictionary;

    /**
     * @param string $uuid
     * @return Dictionary|null
     */
    public function findByUuid(string $uuid): ?Dictionary;
}
