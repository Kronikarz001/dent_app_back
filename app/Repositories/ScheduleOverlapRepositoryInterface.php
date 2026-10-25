<?php

namespace App\Repositories;

use App\Models\Calendar;

/**
 * Summary of ScheduleOverlapRepositoryInterface
 */
interface ScheduleOverlapRepositoryInterface
{
    /**
     * @param Calendar $entry
     * @param string $relation
     * @param string[] $uuids
     * @return bool
     */
    public function hasScheduleOverlap(Calendar $entry, string $relation, array $uuids): bool;
}
