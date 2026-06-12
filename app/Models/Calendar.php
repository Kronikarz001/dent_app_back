<?php

namespace App\Models;

/**
 * Summary of Calendar
 */
class Calendar extends UuidModel
{
    /**
     * @var string[]
     */
    protected $guarded = [];

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
