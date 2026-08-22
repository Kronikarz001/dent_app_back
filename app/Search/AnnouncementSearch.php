<?php

namespace App\Search;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementSearch extends Search
{
    protected function modelClass(): string
    {
        return Announcement::class;
    }

    protected function prefix(): string
    {
        return 'announcement';
    }

    protected function fillableSearchFields(): array
    {
        return [
            'title',
            'user_uuid',
            'published_at',
        ];
    }

    protected function fillableSortFields(): array
    {
        return [
            'published_at',
            'created_at',
        ];
    }

    protected function searchStringFields(): array
    {
        return [
            'title',
            'content',
        ];
    }

    protected function preFilter(Builder $query, array $params): void
    {
        $query->where($params);
    }

    protected function relationsShipLoad(): array
    {
        return [
            'author',
        ];
    }
}
