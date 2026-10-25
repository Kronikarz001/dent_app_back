<?php

namespace App\Services\Concerns;

use App\Exceptions\ScheduleConflictException;
use App\Models\Calendar;
use App\Repositories\ScheduleOverlapRepositoryInterface;

/**
 * Summary of DetectsScheduleConflicts
 */
trait DetectsScheduleConflicts
{
    /**
     * @return ScheduleOverlapRepositoryInterface
     */
    abstract protected function scheduleRepository(): ScheduleOverlapRepositoryInterface;

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
        if ($this->scheduleRepository()->hasScheduleOverlap($calendar, 'users', $userUuids)
            || $this->scheduleRepository()->hasScheduleOverlap($calendar, 'patients', $patientUuids)) {
            throw new ScheduleConflictException;
        }
    }
}
