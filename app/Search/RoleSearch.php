<?php

namespace App\Search;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of RoleSearch
 */
class RoleSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Role::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'role';
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
