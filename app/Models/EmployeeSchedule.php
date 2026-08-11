<?php

namespace App\Models;

use App\Enums\CalendarEventType;
use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of EmployeeSchedule
 */
class EmployeeSchedule extends Calendar
{
    /**
     * @var string
     */
    protected $table = 'calendars';

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope('employeeType', function (Builder $query) {
            $query->whereIn($query->getModel()->getTable().'.type', array_map(
                fn (CalendarEventType $type) => $type->value,
                CalendarEventType::employeeTypes()
            ));
        });
    }
}
