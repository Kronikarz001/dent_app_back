<?php

namespace App\Search;

use App\Models\Calendar;
use App\Search\Concerns\FiltersCalendarEntries;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of CalendarSearch
 */
class CalendarSearch extends Search
{
    use FiltersCalendarEntries;

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
            'date',
            'end_date',
            'start_time',
            'end_time',
            'no_show',
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
            'date',
            'end_date',
            'start_time',
            'end_time',
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
            'date',
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
        $this->applyUserUuids($query, $params['user_uuids'] ?? []);
        $this->applyDateRange($query, $params['date_from'] ?? null, $params['date_to'] ?? null);
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'dentalExaminations',
            'users:uuid,first_name,last_name',
            'patients:uuid,first_name,last_name',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaultSort(): array
    {
        return [
            'date' => 'asc',
            'start_time' => 'asc',
        ];
    }
}
