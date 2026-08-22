<?php

namespace App\Search;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PermissionGroupSearch
 */
class PermissionGroupSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return PermissionGroup::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'permissionGroup';
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
        return [
            'permissions',
        ];
    }
}
