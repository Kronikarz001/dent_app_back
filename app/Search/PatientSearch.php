<?php

namespace App\Search;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PatientSearch
 */
class PatientSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Patient::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'patient';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'pesel',
            'is_active',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'pesel',
            'is_active',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'pesel',
            'is_active',
        ];
    }

    /**
     * @param  Builder  $query
     * @param  array  $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void {}

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'phoneNumbers',
        ];
    }
}
