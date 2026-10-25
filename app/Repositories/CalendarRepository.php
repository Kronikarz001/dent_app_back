<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Models\Patient;
use App\Repositories\Concerns\FindsScheduleOverlaps;
use App\Search\CalendarSearch;
use App\Search\PatientVisitSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Summary of CalendarRepository
 *
 * @extends SearchableRepository<Calendar>
 */
class CalendarRepository extends SearchableRepository implements CalendarRepositoryInterface
{
    use FindsScheduleOverlaps;

    /**
     * @var class-string<Calendar>
     */
    protected string $modelClass = Calendar::class;

    /**
     * @param CalendarSearch $search
     * @param PatientVisitSearch $patientVisitSearch
     */
    public function __construct(
        private readonly CalendarSearch $search,
        private readonly PatientVisitSearch $patientVisitSearch,
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findLastVisitForPatient(Patient $patient): ?Calendar
    {
        return $this->whereStarted($this->forPatient($patient), true)
            ->where('no_show', false)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->first();
    }

    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findNextVisitForPatient(Patient $patient): ?Calendar
    {
        return $this->whereStarted($this->forPatient($patient), false)
            ->orderBy('date')
            ->orderBy('start_time')
            ->first();
    }

    /**
     * @param Patient $patient
     * @return Calendar|null
     */
    public function findLastNoShowForPatient(Patient $patient): ?Calendar
    {
        return $this->forPatient($patient)
            ->where('no_show', true)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->first();
    }

    /**
     * @param Patient $patient
     * @param bool|null $noShow
     * @return int
     */
    public function countForPatient(Patient $patient, ?bool $noShow = null): int
    {
        return $this->forPatient($patient)
            ->when($noShow !== null, fn (Builder $query) => $query->where('no_show', $noShow))
            ->count();
    }

    /**
     * @param Patient $patient
     * @return LengthAwarePaginator
     */
    public function findVisitsForPatient(Patient $patient): LengthAwarePaginator
    {
        return $this->patientVisitSearch->search(['patient_uuid' => $patient->uuid]);
    }

    /**
     * @param Patient $patient
     * @return Builder
     */
    private function forPatient(Patient $patient): Builder
    {
        return $this->query()
            ->whereHas('patients', fn (Builder $query) => $query->where('patients.uuid', $patient->uuid))
            ->with([
                'users' => fn ($query) => $query->orderBy('calendar_users.assigned_at')->with('jobPosition'),
                'dentalExaminations' => fn ($query) => $query->orderBy('name'),
            ]);
    }

    /**
     * @param Builder $query
     * @param bool $started
     * @return Builder
     */
    private function whereStarted(Builder $query, bool $started): Builder
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $time = $now->format('H:i:s');

        return $query->where(function (Builder $query) use ($started, $today, $time) {
            $query->where('date', $started ? '<' : '>', $today)
                ->orWhere(function (Builder $query) use ($started, $today, $time) {
                    $query->where('date', $today)
                        ->whereRaw('COALESCE(start_time, ?) '.($started ? '<=' : '>').' ?', ['00:00:00', $time]);
                });
        });
    }
}
