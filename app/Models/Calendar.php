<?php

namespace App\Models;

use App\Traits\HasFile;

/**
 * Summary of Calendar
 */
class Calendar extends UuidModel
{
    use HasFile;

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
}
