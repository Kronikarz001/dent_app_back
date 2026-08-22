<?php

namespace App\Models;

use App\Enums\CalendarEventType;
use App\Traits\Auditable;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Summary of Calendar
 */
class Calendar extends UuidModel
{
    use Auditable, HasFile;

    protected $fillable = [
        'name',
        'description',
        'type',
        'date',
        'end_date',
        'start_time',
        'end_time',
        'no_show',
        'is_active',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'end_date' => 'date',
            'no_show' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope('appointmentType', function (Builder $query) {
            $query->whereIn($query->getModel()->getTable().'.type', array_map(
                fn (CalendarEventType $type) => $type->value,
                CalendarEventType::appointmentTypes()
            ));
        });
    }

    /**
     * @return MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'userable', 'calendar_users', 'calendar_uuid');
    }

    /**
     * @return MorphToMany
     */
    public function patients(): MorphToMany
    {
        return $this->morphedByMany(Patient::class, 'userable', 'calendar_users', 'calendar_uuid');
    }

    /**
     * @return BelongsToMany
     */
    public function dentalExaminations(): BelongsToMany
    {
        return $this->belongsToMany(
            DentalExamination::class,
            'calendars_dental_examinations',
            'calendar_uuid',
            'dental_examination_uuid',
            'uuid',
            'uuid'
        );
    }
}
