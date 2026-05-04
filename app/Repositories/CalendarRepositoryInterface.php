<?php
namespace App\Repositories;

use App\Models\Calendar;
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
}
