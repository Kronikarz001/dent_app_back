<?php

namespace App\Search\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of FiltersCalendarEntries
 */
trait FiltersCalendarEntries
{
    /**
     * @param Builder $query
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return void
     */
    protected function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        $table = $query->getModel()->getTable();

        if ($dateTo !== null) {
            $query->whereDate($table.'.date', '<=', $dateTo);
        }

        if ($dateFrom !== null) {
            $query->whereRaw('COALESCE('.$table.'.end_date, '.$table.'.date) >= ?', [$dateFrom]);
        }
    }

    /**
     * @param Builder $query
     * @param string[] $userUuids
     * @return void
     */
    protected function applyUserUuids(Builder $query, array $userUuids): void
    {
        if ($userUuids === []) {
            return;
        }

        $query->whereHas('users', fn (Builder $users) => $users->whereIn('users.uuid', $userUuids));
    }
}
