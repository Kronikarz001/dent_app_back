<?php

namespace App\Search;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PhotoSearch
 */
class PhotoSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Photo::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'photos';
    }

    /**
     * @return array
     */
    protected function fillableSearchFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @return array
     */
    protected function fillableSortFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @return array
     */
    protected function searchStringFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $query->where($params);
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'user',
        ];
    }
}
