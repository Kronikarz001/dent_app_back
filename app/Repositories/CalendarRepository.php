<?php

namespace App\Repositories;

use App\Models\Calendar;
use App\Search\CalendarSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

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
     * @param Calendar|Model $model
     * @return bool
     */
    public function delete(Calendar|Model $model): bool
    {
        return $model->delete();
    }
}
