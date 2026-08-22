<?php

namespace App\Search;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PermissionSearch
 */
class PermissionSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Permission::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'permission';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'resource',
            'type',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'resource',
            'type',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'resource',
            'type',
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
