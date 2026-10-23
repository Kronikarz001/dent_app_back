<?php

namespace App\Search;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PatientVisitSearch
 */
class PatientVisitSearch extends Search
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
        return 'visit';
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
            'start_time',
            'no_show',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'name',
            'type',
            'date',
            'start_time',
            'no_show',
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
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $query->whereHas('patients', fn (Builder $query) => $query->where('patients.uuid', $params['patient_uuid']));
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'users.jobPosition',
            'dentalExaminations',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaultSort(): array
    {
        return [
            'date' => 'desc',
            'start_time' => 'desc',
        ];
    }
}
