<?php

namespace App\Search;

use App\Models\ScheduleRule;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of ScheduleRuleSearch
 */
class ScheduleRuleSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return ScheduleRule::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'scheduleRule';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'day',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'day',
            'start_time',
            'end_time',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $query->where('schedule_rules.user_uuid', $params['user_uuid']);
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function defaultSort(): array
    {
        return [
            'start_time' => 'asc',
        ];
    }
}
