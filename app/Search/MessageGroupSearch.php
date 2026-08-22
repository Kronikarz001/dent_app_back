<?php

namespace App\Search;

use App\Models\MessageGroup;
use Illuminate\Database\Eloquent\Builder;

class MessageGroupSearch extends Search
{
    protected function modelClass(): string
    {
        return MessageGroup::class;
    }

    protected function prefix(): string
    {
        return 'messageGroup';
    }

    protected function fillableSearchFields(): array
    {
        return [
            'name',
            'creator_uuid',
            'is_default',
        ];
    }

    protected function fillableSortFields(): array
    {
        return [
            'name',
            'created_at',
        ];
    }

    protected function searchStringFields(): array
    {
        return [
            'name',
        ];
    }

    protected function preFilter(Builder $query, array $params): void
    {
        $forUser = $params['for_user'] ?? null;
        unset($params['for_user']);

        if ($forUser !== null) {
            $query->whereHas('users', function (Builder $q) use ($forUser) {
                $q->where('users.uuid', $forUser);
            });
        }

        $query->where($params);
    }

    protected function relationsShipLoad(): array
    {
        return [
            'creator',
            'users',
        ];
    }
}
