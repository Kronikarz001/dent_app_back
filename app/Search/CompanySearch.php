<?php

namespace App\Search;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of CompanySearch
 */
class CompanySearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Company::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'company';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'regon',
            'nip',
            'province',
            'district',
            'municipality',
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
            'regon',
            'nip',
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
