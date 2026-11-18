<?php

namespace App\Services;

use App\Dto\HoursDistributionDto;
use App\Dto\HoursSummaryDto;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of StatisticsServiceInterface
 */
interface StatisticsServiceInterface
{
    /**
     * @param int $year
     * @param int $month
     * @return LengthAwarePaginator
     */
    public function getUsersStatistics(int $year, int $month): LengthAwarePaginator;

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @return HoursSummaryDto
     */
    public function getUserSummary(User $user, int $year, int $month): HoursSummaryDto;

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @return HoursDistributionDto[]
     */
    public function getUserDistribution(User $user, int $year, int $month): array;

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserDailyHours(User $user, int $year, int $month, int $page, int $perPage): LengthAwarePaginator;
}
