<?php
namespace App\Search;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of CalendarSearch
 */
class CalendarSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Calendar::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'calendar';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'description',
            'type',
            'start_date',
            'end_date',
            'is_active',
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
            'description',
            'type',
            'start_date',
            'end_date',
            'is_active',
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
            'type',
            'start_date',
            'end_date',
            'is_active',
            'created_at',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [];
    }
}
