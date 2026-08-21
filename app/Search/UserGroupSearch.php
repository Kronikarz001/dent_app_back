<?php

namespace App\Search;

use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of UserGroupSearch
 */
class UserGroupSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return UserGroup::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'userGroup';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'name',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'name',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void {}

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [];
    }
}
