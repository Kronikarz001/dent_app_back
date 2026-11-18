<?php

namespace App\Search;

use App\Models\Room;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of RoomSearch
 */
class RoomSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Room::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'room';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'is_active',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'name',
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
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        if (array_key_exists('is_active', $params)) {
            $query->where('rooms.is_active', $params['is_active']);
        }
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
            'name' => 'asc',
        ];
    }
}
