<?php

namespace Tests\Unit\Enums;

use App\Enums\VisitStatus;
use App\Models\Calendar;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Summary of VisitStatusTest
 */
class VisitStatusTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
    }

    /**
     * @return void
     */
    public function testPastVisitIsCompleted(): void
    {
        $this->assertSame(VisitStatus::COMPLETED, VisitStatus::fromCalendar($this->visit('2026-10-21', '11:59:00')));
    }

    /**
     * @return void
     */
    public function testVisitStartingNowIsCompleted(): void
    {
        $this->assertSame(VisitStatus::COMPLETED, VisitStatus::fromCalendar($this->visit('2026-10-21', '12:00:00')));
    }

    /**
     * @return void
     */
    public function testFutureVisitIsScheduled(): void
    {
        $this->assertSame(VisitStatus::SCHEDULED, VisitStatus::fromCalendar($this->visit('2026-10-21', '12:01:00')));
    }

    /**
     * @return void
     */
    public function testNoShowWinsOverTime(): void
    {
        $this->assertSame(VisitStatus::NO_SHOW, VisitStatus::fromCalendar($this->visit('2026-10-02', '12:00:00', true)));
        $this->assertSame(VisitStatus::NO_SHOW, VisitStatus::fromCalendar($this->visit('2026-10-28', '12:00:00', true)));
    }

    /**
     * @return void
     */
    public function testEveryStatusHasPolishName(): void
    {
        $this->assertSame('Odbyta', VisitStatus::COMPLETED->name());
        $this->assertSame('Nieobecność', VisitStatus::NO_SHOW->name());
        $this->assertSame('Zaplanowana', VisitStatus::SCHEDULED->name());
    }

    /**
     * @param string $date
     * @param string $startTime
     * @param bool $noShow
     * @return Calendar
     */
    private function visit(string $date, string $startTime, bool $noShow = false): Calendar
    {
        return Calendar::factory()->make([
            'date' => $date,
            'start_time' => $startTime,
            'no_show' => $noShow,
        ]);
    }
}
