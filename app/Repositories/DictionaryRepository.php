<?php

namespace App\Repositories;

use App\Models\Dictionary;
use App\Search\DictionarySearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DictionaryRepository
 */
abstract class DictionaryRepository extends SearchableRepository implements DictionaryRepositoryInterface
{
    /**
     * @param DictionarySearch $search
     */
    public function __construct(
        private readonly DictionarySearch $search,
    ) {}

    /**
     * @return string
     */
    abstract protected function getModelClass(): string;

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param array $data
     * @return Dictionary
     */
    public function create(array $data): Dictionary
    {
        return $this->getModelClass()::create($data);
    }

    /**
     * @param Dictionary|Model $model
     * @param array $data
     * @return Dictionary
     */
    public function update(Dictionary|Model $model, array $data): Dictionary
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param string $uuid
     * @return Dictionary|null
     */
    public function findByUuid(string $uuid): ?Dictionary
    {
        return $this->getModelClass()::where('uuid', $uuid)->first();
    }
}
