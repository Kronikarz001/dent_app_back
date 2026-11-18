<?php

namespace App\Dto;

use App\Enums\HoursType;

/**
 * Summary of DailyHoursDto
 */
class DailyHoursDto extends Dto
{
    /**
     * @param string $userUuid
     * @param string $date
     * @param HoursType $type
     * @param string $source
     * @param float $hours
     */
    public function __construct(
        private readonly string $userUuid,
        private readonly string $date,
        private readonly HoursType $type,
        private readonly string $source,
        private readonly float $hours,
    ) {}

    /**
     * @return string
     */
    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return HoursType
     */
    public function getType(): HoursType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return float
     */
    public function getHours(): float
    {
        return $this->hours;
    }
}
