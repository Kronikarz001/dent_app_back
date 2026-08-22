<?php

namespace App\Search;

use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of DictionarySearch
 */
abstract class DictionarySearch extends Search
{
    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'dictionaries';
    }

    /**
     * @return array
     */
    protected function fillableSearchFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function fillableSortFields(): array
    {
        return [
            'value',
            'key',
        ];
    }

    /**
     * @return array
     */
    protected function searchStringFields(): array
    {
        return [
            'value',
            'key',
            'additional',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $query->where('type', $this->modelClass());
        $query->where($params);
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function jsonSearchableFields(): array
    {
        return [
            'additional',
        ];
    }
}
