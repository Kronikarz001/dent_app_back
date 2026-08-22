<?php

namespace App\Search;

use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;

class MessageSearch extends Search
{
    protected function modelClass(): string
    {
        return Message::class;
    }

    protected function prefix(): string
    {
        return 'message';
    }

    protected function fillableSearchFields(): array
    {
        return [
            'message',
            'user_uuid',
            'recipient_user_uuid',
            'message_group_uuid',
        ];
    }

    protected function fillableSortFields(): array
    {
        return [
            'created_at',
        ];
    }

    protected function searchStringFields(): array
    {
        return [
            'message',
        ];
    }

    protected function preFilter(Builder $query, array $params): void
    {
        $forUser = $params['for_user'] ?? null;
        $userGroupUuids = $params['user_group_uuids'] ?? [];
        unset($params['for_user'], $params['user_group_uuids']);

        if ($forUser !== null) {
            $query->where(function (Builder $inbox) use ($forUser, $userGroupUuids) {
                $inbox->where('user_uuid', $forUser)
                    ->orWhere('recipient_user_uuid', $forUser)
                    ->orWhereIn('message_group_uuid', $userGroupUuids);
            });
        }

        $query->where($params);
    }

    protected function relationsShipLoad(): array
    {
        return [
            'sender',
            'recipient',
            'files',
        ];
    }
}
