<?php

namespace App\Search;

use App\Models\File;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of FileSearch
 */
class FileSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return File::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'files';
    }

    /**
     * @return array
     */
    protected function fillableSearchFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @return array
     */
    protected function fillableSortFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @return array
     */
    protected function searchStringFields(): array
    {
        return [
            'filename',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $excludeUuids = $params['exclude_uuids'] ?? [];
        unset($params['exclude_uuids']);

        if (is_array($excludeUuids) && ! empty($excludeUuids)) {
            $query->whereNotIn('uuid', $excludeUuids);
        }

        $query->where($params);
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'user',
            'files',
            'files.user',
        ];
    }
}
