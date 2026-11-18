<?php

namespace Tests\Feature\Controllers;

use App\Models\EmployeeSchedule;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of StatisticsControllerTest
 */
class StatisticsControllerTest extends TestCase
{
    /**
     * @param User $user
     * @param array $attributes
     * @return EmployeeSchedule
     */
    private function scheduleFor(User $user, array $attributes): EmployeeSchedule
    {
        $schedule = EmployeeSchedule::factory()->create(array_merge(['is_active' => true], $attributes));
        $schedule->users()->attach($user->uuid);

        return $schedule;
    }

    /**
     * @return User
     */
    private function employeeWithNovemberEntries(): User
    {
        $employee = User::factory()->create();

        $this->scheduleFor($employee, ['type' => 'WORK', 'date' => '2026-11-02', 'start_time' => '08:00', 'end_time' => '16:00']);
        $this->scheduleFor($employee, ['type' => 'WORK', 'date' => '2026-11-03', 'start_time' => '08:00', 'end_time' => '12:30']);
        $this->scheduleFor($employee, [
            'type' => 'VACATION',
            'date' => '2026-11-05',
            'end_date' => '2026-11-09',
            'start_time' => null,
            'end_time' => null,
        ]);
        $this->scheduleFor($employee, ['type' => 'SICK_LEAVE', 'date' => '2026-11-30', 'start_time' => null, 'end_time' => null]);
        $this->scheduleFor($employee, ['type' => 'WORK', 'date' => '2026-12-01', 'start_time' => '08:00', 'end_time' => '16:00']);
        $this->scheduleFor($employee, ['type' => 'WORK', 'date' => '2026-11-04', 'start_time' => '08:00', 'end_time' => '16:00', 'is_active' => false]);
        $this->scheduleFor($employee, ['type' => 'ABSENCE', 'date' => '2026-11-06', 'start_time' => '08:00', 'end_time' => '16:00']);

        return $employee;
    }

    /**
     * @return void
     */
    public function testSummaryAggregatesHoursByTypeWithinMonth(): void
    {
        $employee = $this->employeeWithNovemberEntries();

        $this->callApiWithLoggedUser()
            ->getJson(route('statistics.summary', ['user' => $employee->uuid, 'year' => 2026, 'month' => 11]))
            ->assertOk()
            ->assertExactJson(['praca' => 12.5, 'l4' => 8, 'urlop' => 24]);
    }

    /**
     * @return void
     */
    public function testDistributionReturnsPercentages(): void
    {
        $employee = $this->employeeWithNovemberEntries();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('statistics.distribution', ['user' => $employee->uuid, 'year' => 2026, 'month' => 11]));

        $response->assertOk();
        $response->assertExactJson([
            ['type' => 'praca', 'hours' => 12.5, 'percent' => 28.09],
            ['type' => 'l4', 'hours' => 8, 'percent' => 17.98],
            ['type' => 'urlop', 'hours' => 24, 'percent' => 53.93],
        ]);
    }

    /**
     * @return void
     */
    public function testDistributionWithoutEntriesReturnsZeroPercent(): void
    {
        $employee = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('statistics.distribution', ['user' => $employee->uuid, 'year' => 2026, 'month' => 11]))
            ->assertOk()
            ->assertJsonPath('0.percent', 0);
    }

    /**
     * @return void
     */
    public function testDailyReturnsPaginatedEntriesSortedByDate(): void
    {
        $employee = $this->employeeWithNovemberEntries();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('statistics.daily', [
                'user' => $employee->uuid,
                'year' => 2026,
                'month' => 11,
                'perPage' => 2,
            ]));

        $response->assertOk();
        $response->assertJsonPath('total', 6);
        $response->assertJsonPath('last_page', 3);
        $response->assertJsonPath('data.0.date', '2026-11-02');
        $response->assertJsonPath('data.0.type', 'praca');
        $response->assertJsonPath('data.0.hours', 8);
        $response->assertJsonPath('data.1.date', '2026-11-03');
        $response->assertJsonPath('data.1.hours', 4.5);
    }

    /**
     * @return void
     */
    public function testUsersReturnsPaginatedStatisticsPerUser(): void
    {
        $employee = $this->employeeWithNovemberEntries();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('statistics.users', [
                'year' => 2026,
                'month' => 11,
                'user' => ['uuid' => $employee->uuid],
            ]));

        $response->assertOk();
        $response->assertJsonPath('total', 1);
        $response->assertJsonPath('data.0.user.uuid', $employee->uuid);
        $response->assertJsonPath('data.0.work_hours', 12.5);
        $response->assertJsonPath('data.0.vacation_hours', 24);
        $response->assertJsonPath('data.0.sick_leave_hours', 8);
    }

    /**
     * @return void
     */
    public function testInvalidMonthReturnsValidationError(): void
    {
        $employee = User::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('statistics.summary', ['user' => $employee->uuid, 'year' => 2026, 'month' => 13]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('month');
    }
}
