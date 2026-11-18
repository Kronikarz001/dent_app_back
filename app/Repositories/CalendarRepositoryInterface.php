<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of CalendarRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Calendar>
 */
interface CalendarRepositoryInterface extends ScheduleOverlapRepositoryInterface, SearchableRepositoryInterface
{
    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findLastVisitForPatient(Patient $patient): ?Calendar;

    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findNextVisitForPatient(Patient $patient): ?Calendar;

    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findLastNoShowForPatient(Patient $patient): ?Calendar;

    /**
     * @param Patient $patient
     * @param bool|null $noShow
     * @return int
     */
    public function countForPatient(Patient $patient, ?bool $noShow = null): int;

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator<int, Calendar>
     */
    public function findVisitsForPatient(Patient $patient): LengthAwarePaginator;
}
