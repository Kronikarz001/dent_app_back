<?php

namespace App\Search;

use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of MessageSearch
 */
class MessageSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return Message::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'message';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'message',
            'user_uuid',
            'recipient_user_uuid',
            'message_group_uuid',
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
        return [
            'message',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $forUser = $params['for_user'] ?? null;
        unset($params['for_user']);

        if ($forUser !== null) {
            $query->where(function (Builder $inbox) use ($forUser) {
                $inbox->where('user_uuid', $forUser)
                    ->orWhere('recipient_user_uuid', $forUser)
                    ->orWhereNotNull('message_group_uuid');
            });
        }

        $query->where($params);
    }

    /**
     * @return string[]
     */
    protected function relationsShipLoad(): array
    {
        return [
            'sender',
            'recipient',
        ];
    }
}
