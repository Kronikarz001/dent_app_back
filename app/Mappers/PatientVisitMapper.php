<?php

namespace App\Mappers;

use App\Dto\PatientVisitDto;
use App\Enums\VisitStatus;
use App\Models\Calendar;

/**
 * Summary of PatientVisitMapper
 */
class PatientVisitMapper implements MapperInterface
{
    /**
     * @param Calendar $calendar
     * @return PatientVisitDto
     */
    public function map(Calendar $calendar): PatientVisitDto
    {
        return new PatientVisitDto(
            $calendar->uuid,
            $calendar->startsAt(),
            $calendar->users->first(),
            $calendar->dentalExaminations,
            VisitStatus::fromCalendar($calendar),
        );
    }
}
