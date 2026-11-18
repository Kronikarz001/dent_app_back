<?php

namespace App\Search;

use App\Enums\CalendarEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of UserSearch
 */
class UserSearch extends Search
{
    /**
     * @return string
     */
    protected function modelClass(): string
    {
        return User::class;
    }

    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'user';
    }

    /**
     * @return string[]
     */
    protected function fillableSearchFields(): array
    {
        return [
            'uuid',
            'email',
            'private_email',
            'first_name',
            'last_name',
            'pesel',
            'is_active',
        ];
    }

    /**
     * @return string[]
     */
    protected function fillableSortFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'private_email',
            'created_at',
            'is_active',
        ];
    }

    /**
     * @return string[]
     */
    protected function searchStringFields(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'private_email',
            'created_at',
            'is_active',
        ];
    }

    /**
     * @param Builder $query
     * @param array $params
     * @return void
     */
    protected function preFilter(Builder $query, array $params): void
    {
        $workingOn = $params['working_on'] ?? null;

        if ($workingOn !== null) {
            $query->whereHas('employeeSchedules', fn (Builder $schedules) => $schedules
                ->where('calendars.type', CalendarEventType::WORK->value)
                ->where('calendars.is_active', true)
                ->whereDate('calendars.date', '<=', $workingOn)
                ->whereRaw('COALESCE(calendars.end_date, calendars.date) >= ?', [$workingOn]));
        }
    }

    /**
     * @return string[]
     */
    protected function selectRelations(): array
    {
        return [
            'jobPosition:uuid,name',
            'latestAvatar',
        ];
    }

    /**
     * @return array
     */
    protected function relationsShipLoad(): array
    {
        return [
            'phoneNumbers',
        ];
    }
}
