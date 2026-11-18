<?php

namespace App\Mappers;

use App\Dto\DailyHoursDto;
use App\Enums\CalendarEventType;
use App\Enums\HoursType;
use App\Models\EmployeeSchedule;
use App\Models\User;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Summary of EmployeeScheduleHoursMapper
 */
class EmployeeScheduleHoursMapper implements MapperInterface
{
    /**
     * @var float
     */
    public const FULL_DAY_HOURS = 8.0;

    /**
     * @param EmployeeSchedule $schedule
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection<int, DailyHoursDto>
     */
    public function map(EmployeeSchedule $schedule, Carbon $from, Carbon $to): Collection
    {
        $type = HoursType::fromCalendarEventType(CalendarEventType::from($schedule->type));

        if ($type === null) {
            return collect();
        }

        $start = $schedule->date->copy()->max($from);
        $end = ($schedule->end_date ?? $schedule->date)->copy()->min($to);

        if ($start->gt($end)) {
            return collect();
        }

        $hasHours = $schedule->start_time !== null && $schedule->end_time !== null;
        $hoursPerDay = $hasHours ? $this->hoursBetween($schedule->start_time, $schedule->end_time) : self::FULL_DAY_HOURS;
        $source = $schedule->name ?? CalendarEventType::from($schedule->type)->name();

        return collect(CarbonPeriod::create($start, $end))
            ->filter(fn (CarbonInterface $day) => $hasHours || $day->isWeekday())
            ->flatMap(fn (CarbonInterface $day) => $schedule->users->map(fn (User $user) => new DailyHoursDto(
                $user->uuid,
                $day->toDateString(),
                $type,
                $source,
                $hoursPerDay,
            )))
            ->values();
    }

    /**
     * @param string $startTime
     * @param string $endTime
     * @return float
     */
    private function hoursBetween(string $startTime, string $endTime): float
    {
        return round(Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)) / 60, 2);
    }
}
