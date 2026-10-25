<?php

namespace App\Search;

use App\Models\Prescription;
use App\Models\User;
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
    protected function preFilter(Builder $query, array $params): void
    {
        $query->where($params);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultSort(): array
    {
        return [
            'created_at' => 'desc',
        ];
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'patient.phoneNumbers',
            ...array_map(fn (string $relation) => 'patient.doctor.'.$relation, User::RESOURCE_RELATIONS),
            ...array_map(fn (string $relation) => 'doctor.'.$relation, User::RESOURCE_RELATIONS),
            'medicines',
        ];
    }
}
