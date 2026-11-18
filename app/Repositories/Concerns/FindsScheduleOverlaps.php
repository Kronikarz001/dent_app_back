<?php

namespace App\Repositories\Concerns;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of FindsScheduleOverlaps
 */
trait FindsScheduleOverlaps
{
    /**
     * @param Calendar $entry
     * @param string $relation
     * @param string[] $uuids
     * @return bool
     */
    public function hasScheduleOverlap(Calendar $entry, string $relation, array $uuids): bool
    {
        if (empty($uuids)) {
            return false;
        }

        $rangeStart = $entry->date;
        $rangeEnd = $entry->end_date ?? $entry->date;
        $isWholeDay = is_null($entry->start_time) || is_null($entry->end_time);

        return $this->query()
            ->where('uuid', '!=', $entry->uuid)
            ->where('is_active', true)
            ->whereHas($relation, fn (Builder $q) => $q->whereIn('uuid', $uuids))
            ->where('date', '<=', $rangeEnd)
            ->where(fn (Builder $q) => $q->where('end_date', '>=', $rangeStart)
                ->orWhere(fn (Builder $q2) => $q2->whereNull('end_date')->where('date', '>=', $rangeStart)))
            ->when(! $isWholeDay, fn (Builder $query) => $query->where(
                fn (Builder $q) => $q->whereNull('start_time')
                    ->orWhereNull('end_time')
                    ->orWhere(fn (Builder $q2) => $q2->where('start_time', '<', $entry->end_time)->where('end_time', '>', $entry->start_time))
            ))
            ->exists();
    }
}
