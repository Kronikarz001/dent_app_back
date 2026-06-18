<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasFile;
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
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'ownerable_uuid' => 'string',
            'connected_calendar_uuid' => 'string',
            'start_date' => 'timestamp',
            'end_date' => 'timestamp',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'userable', 'calendar_users');
    }

    /**
     * @return MorphToMany
     */
    public function patients(): MorphToMany
    {
        return $this->morphedByMany(Patient::class, 'userable', 'calendar_users');
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
