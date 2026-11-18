<?php

namespace App\Dto;

use App\Enums\HoursType;

/**
 * Summary of HoursDistributionDto
 */
class HoursDistributionDto extends Dto
{
    /**
     * @param HoursType $type
     * @param float $hours
     * @param float $percent
     */
    public function __construct(
        private readonly HoursType $type,
        private readonly float $hours,
        private readonly float $percent,
    ) {}

    /**
     * @return HoursType
     */
    public function getType(): HoursType
    {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getHours(): float
    {
        return $this->hours;
    }

    /**
     * @return float
     */
    public function getPercent(): float
    {
        return $this->percent;
    }
}
