<?php

namespace App\Search;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of AuditSearch
 */
class AuditSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Audit::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'audit';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'type',
            'user_uuid',
            'auditable_type',
            'auditable_id',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'type',
            'created_at',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'type',
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
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'user',
        ];
    }
}
