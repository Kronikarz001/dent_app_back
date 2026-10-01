<?php

namespace App\Search;

use App\Models\Prescription;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of PrescriptionSearch
 */
class PrescriptionSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Prescription::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'prescription';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'code',
            'status',
            'patient_uuid',
            'doctor_uuid',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'name',
            'code',
            'status',
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
            'code',
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
            'patient',
            'doctor',
            'medicines',
        ];
    }
}
