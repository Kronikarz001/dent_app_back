<?php

namespace App\Search;

use App\Models\Dentist;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of DentistSearch
 */
class DentistSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Dentist::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'dentist';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'uuid',
            'email',
            'private_email',
            'first_name',
            'last_name',
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
            'private_email',
            'created_at',
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
            'private_email',
            'created_at',
            'is_active',
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
            'phoneNumbers',
            'jobPosition',
        ];
    }
}
