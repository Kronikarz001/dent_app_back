<?php

namespace App\Http\Requests;

/**
 * Summary of CalendarIndexRequest
 */
class CalendarIndexRequest extends CalendarEntriesIndexRequest
{
    /**
     * @return string
     */
    protected function prefix(): string
    {
        return 'calendar';
    }
}
