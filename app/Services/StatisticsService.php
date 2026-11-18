<?php

namespace App\Services;

use App\Dto\DailyHoursDto;
use App\Dto\HoursDistributionDto;
use App\Dto\HoursSummaryDto;
use App\Dto\StatisticUserDto;
use App\Enums\HoursType;
use App\Mappers\EmployeeScheduleHoursMapper;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Repositories\EmployeeScheduleRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Summary of StatisticsService
 */
readonly class StatisticsService implements StatisticsServiceInterface
{
    /**
     * @param UserRepositoryInterface $userRepository
     * @param EmployeeScheduleRepositoryInterface $employeeScheduleRepository
     * @param EmployeeScheduleHoursMapper $employeeScheduleHoursMapper
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmployeeScheduleRepositoryInterface $employeeScheduleRepository,
        private EmployeeScheduleHoursMapper $employeeScheduleHoursMapper,
    ) {}

    /**
     * @param int $year
     * @param int $month
     * @return LengthAwarePaginator
     */
    public function getUsersStatistics(int $year, int $month): LengthAwarePaginator
    {
        $users = $this->userRepository->findAllWithPagination();
        $this->userRepository->loadRelationsForMany($users->getCollection(), ['jobPosition', 'latestAvatar']);
        $hoursByUser = $this->dailyHours($users->getCollection()->pluck('uuid')->all(), $year, $month)
            ->groupBy(fn (DailyHoursDto $entry) => $entry->getUserUuid());

        return $users->through(fn (User $user) => new StatisticUserDto(
            $user,
            $this->summarize($hoursByUser->get($user->uuid, collect())),
        ));
    }

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @return HoursSummaryDto
     */
    public function getUserSummary(User $user, int $year, int $month): HoursSummaryDto
    {
        return $this->summarize($this->dailyHours([$user->uuid], $year, $month));
    }

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @return HoursDistributionDto[]
     */
    public function getUserDistribution(User $user, int $year, int $month): array
    {
        $summary = $this->getUserSummary($user, $year, $month);
        $total = $summary->getTotalHours();

        return array_map(fn (HoursType $type) => new HoursDistributionDto(
            $type,
            $this->hoursOfType($summary, $type),
            $total > 0 ? round($this->hoursOfType($summary, $type) / $total * 100, 2) : 0.0,
        ), HoursType::cases());
    }

    /**
     * @param User $user
     * @param int $year
     * @param int $month
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserDailyHours(User $user, int $year, int $month, int $page, int $perPage): LengthAwarePaginator
    {
        $entries = $this->dailyHours([$user->uuid], $year, $month)
            ->sortBy(fn (DailyHoursDto $entry) => $entry->getDate())
            ->values();

        return new LengthAwarePaginator(
            $entries->forPage($page, $perPage)->values(),
            $entries->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * @param string[] $userUuids
     * @param int $year
     * @param int $month
     * @return Collection<int, DailyHoursDto>
     */
    private function dailyHours(array $userUuids, int $year, int $month): Collection
    {
        if ($userUuids === []) {
            return collect();
        }

        $from = Carbon::create($year, $month)->startOfMonth();
        $to = $from->copy()->endOfMonth()->startOfDay();

        return $this->employeeScheduleRepository
            ->findActiveForUsersBetween($userUuids, $from->toDateString(), $to->toDateString())
            ->toBase()
            ->flatMap(fn (EmployeeSchedule $schedule) => $this->employeeScheduleHoursMapper->map($schedule, $from, $to));
    }

    /**
     * @param Collection<int, DailyHoursDto> $entries
     * @return HoursSummaryDto
     */
    private function summarize(Collection $entries): HoursSummaryDto
    {
        $sum = fn (HoursType $type) => round(
            $entries->filter(fn (DailyHoursDto $entry) => $entry->getType() === $type)
                ->sum(fn (DailyHoursDto $entry) => $entry->getHours()),
            2
        );

        return new HoursSummaryDto($sum(HoursType::PRACA), $sum(HoursType::L4), $sum(HoursType::URLOP));
    }

    /**
     * @param HoursSummaryDto $summary
     * @param HoursType $type
     * @return float
     */
    private function hoursOfType(HoursSummaryDto $summary, HoursType $type): float
    {
        return match ($type) {
            HoursType::PRACA => $summary->getWorkHours(),
            HoursType::L4 => $summary->getSickLeaveHours(),
            HoursType::URLOP => $summary->getVacationHours(),
        };
    }
}
