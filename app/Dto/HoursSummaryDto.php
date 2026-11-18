<?php

namespace App\Dto;

/**
 * Summary of HoursSummaryDto
 */
class HoursSummaryDto extends Dto
{
    /**
     * @param float $workHours
     * @param float $sickLeaveHours
     * @param float $vacationHours
     */
    public function __construct(
        private readonly float $workHours,
        private readonly float $sickLeaveHours,
        private readonly float $vacationHours,
    ) {}

    /**
     * @return float
     */
    public function getWorkHours(): float
    {
        return $this->workHours;
    }

    /**
     * @return float
     */
    public function getSickLeaveHours(): float
    {
        return $this->sickLeaveHours;
    }

    /**
     * @return float
     */
    public function getVacationHours(): float
    {
        return $this->vacationHours;
    }

    /**
     * @return float
     */
    public function getTotalHours(): float
    {
        return $this->workHours + $this->sickLeaveHours + $this->vacationHours;
    }
}
