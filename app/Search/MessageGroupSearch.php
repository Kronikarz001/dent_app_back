<?php

namespace App\Search;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of MessageGroupSearch
 */
class MessageGroupSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return MessageGroup::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'messageGroup';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'message_uuid',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
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
        $query->where($params);
    }

    /**
     * @return string[]
     */
    protected function relationsShipLoad(): array
    {
        return [
            'rootMessage',
        ];
    }
}
