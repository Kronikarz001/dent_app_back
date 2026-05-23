<?php

namespace App\Search;

use App\Models\JobPosition;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of JobPositionSearch
 */
class JobPositionSearch extends Search
{
    protected function modelClass(): string
    {
        return JobPosition::class;
    }

    protected function prefix(): string
    {
        return 'jobPosition';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'f_name',
            'm_name',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'name',
            'f_name',
            'm_name',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'name',
            'f_name',
            'm_name',
        ];
    }

    protected function preFilter(Builder $query, array $params): void {}

    protected function relationsShipLoad(): array
    {
        return [];
    }
}
