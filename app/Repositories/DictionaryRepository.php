<?php

namespace App\Repositories;

use App\Models\Dictionary;
use App\Search\DictionarySearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of DictionaryRepository
 *
 * @extends SearchableRepository<Dictionary>
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
     * @return class-string<Dictionary>
     */
    abstract protected function getModelClass(): string;

    /**
     * @return Builder<Dictionary>
     */
    protected function query(): Builder
    {
        return $this->getModelClass()::query();
    }

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
