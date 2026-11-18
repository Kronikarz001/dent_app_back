<?php

namespace App\Dto;

use App\Models\User;

/**
 * Summary of StatisticUserDto
 */
class StatisticUserDto extends Dto
{
    /**
     * @param User $user
     * @param HoursSummaryDto $summary
     */
    public function __construct(
        private readonly User $user,
        private readonly HoursSummaryDto $summary,
    ) {}

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return HoursSummaryDto
     */
    public function getSummary(): HoursSummaryDto
    {
        return $this->summary;
    }
}
