<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Models\Patient;
use App\Search\CalendarSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Summary of CalendarRepository
 */
readonly class CalendarRepository implements CalendarRepositoryInterface
{
    /**
     * @param CalendarSearch $search
     */
    public function __construct(
        private CalendarSearch $search
    ) {}

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param string $uuid
     * @return Calendar|null
     */
    public function findByUuid(string $uuid): ?Calendar
    {
        return Calendar::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Calendar
     */
    public function create(array $data): Calendar
    {
        return Calendar::create($data);
    }

    /**
     * @param Calendar|Model $model
     * @param array $data
     * @return Calendar
     */
    public function update(Calendar|Model $model, array $data): Calendar
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return Calendar::whereIn('uuid', $uuids)->get();
    }

    /**
     * @param string $modelClass
     * @param array $uniqueAttributes
     * @param array $values
     * @return Calendar
     */
    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): Calendar
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }

    /**
     * @param Calendar|Model $model
     * @return bool
     */
    public function delete(Calendar|Model $model): bool
    {
        return $model->delete();
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
     * @return Builder
     */
    private function forPatient(Patient $patient): Builder
    {
        return Calendar::query()
            ->whereHas('patients', fn (Builder $query) => $query->where('patients.uuid', $patient->uuid))
            ->with(['users' => fn ($query) => $query->orderBy('calendar_users.assigned_at')->with('jobPosition')]);
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
