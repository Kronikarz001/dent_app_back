<?php

namespace App\Search;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of MedicineSearch
 */
class MedicineSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Medicine::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'medicine';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'ean_gtin',
            'reimbursement_level',
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
            'description',
            'ean_gtin',
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
