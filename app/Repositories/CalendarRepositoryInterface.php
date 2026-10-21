<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of CalendarRepositoryInterface
 */
interface CalendarRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Calendar|null
     */
    public function findByUuid(string $uuid): ?Calendar;

    /**
     * @param array $data
     * @return Calendar
     */
    public function create(array $data): Calendar;

    /**
     * @param Calendar|Model $model
     * @param array $data
     * @return Calendar
     */
    public function update(Calendar|Model $model, array $data): Calendar;

    /**
     * @param Model|Calendar $model
     * @return bool
     */
    public function delete(Model|Calendar $model): bool;

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
}
