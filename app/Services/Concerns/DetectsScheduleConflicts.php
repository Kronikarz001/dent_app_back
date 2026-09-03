<?php

namespace App\Services\Concerns;

use App\Exceptions\ScheduleConflictException;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared overlap check for Calendar/EmployeeSchedule::assignUsers() — used by
 * both CalendarService and EmployeeScheduleService, each querying through
 * their own model (Calendar/EmployeeSchedule) so the check naturally stays
 * within that model's own event-type family via its global scope.
 */
trait DetectsScheduleConflicts
{
    /**
     * @param Calendar $calendar
     * @param array $userUuids
     * @param array $patientUuids
     * @return void
     *
     * @throws ScheduleConflictException
     */
    private function assertNoScheduleConflicts(Calendar $calendar, array $userUuids, array $patientUuids = []): void
    {
        if ($this->hasOverlap($calendar, 'users', $userUuids) || $this->hasOverlap($calendar, 'patients', $patientUuids)) {
            throw new ScheduleConflictException;
        }
    }

    /**
     * @param Calendar $calendar
     * @param string $relation
     * @param array $uuids
     * @return bool
     */
    private function hasOverlap(Calendar $calendar, string $relation, array $uuids): bool
    {
        if (empty($uuids)) {
            return false;
        }

        $rangeStart = $calendar->date;
        $rangeEnd = $calendar->end_date ?? $calendar->date;
        $isWholeDay = is_null($calendar->start_time) || is_null($calendar->end_time);

        return $calendar::query()
            ->where('uuid', '!=', $calendar->uuid)
            ->where('is_active', true)
            ->whereHas($relation, fn (Builder $q) => $q->whereIn('uuid', $uuids))
            ->where('date', '<=', $rangeEnd)
            ->where(fn (Builder $q) => $q->where('end_date', '>=', $rangeStart)
                ->orWhere(fn (Builder $q2) => $q2->whereNull('end_date')->where('date', '>=', $rangeStart)))
            ->when(! $isWholeDay, fn (Builder $query) => $query->where(
                fn (Builder $q) => $q->whereNull('start_time')
                    ->orWhereNull('end_time')
                    ->orWhere(fn (Builder $q2) => $q2->where('start_time', '<', $calendar->end_time)->where('end_time', '>', $calendar->start_time))
            ))
            ->exists();
    }
}
