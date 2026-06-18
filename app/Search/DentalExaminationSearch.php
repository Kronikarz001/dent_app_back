<?php

namespace App\Search;

use App\Models\DentalExamination;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of DentalExaminationSearch
 */
class DentalExaminationSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return DentalExamination::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'dentalExamination';
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
