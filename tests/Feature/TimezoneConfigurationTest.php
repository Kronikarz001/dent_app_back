<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Summary of TimezoneConfigurationTest
 */
class TimezoneConfigurationTest extends TestCase
{
    /**
     * @return void
     */
    public function testApplicationRunsInWarsawTimezone(): void
    {
        $this->assertSame('Europe/Warsaw', config('app.timezone'));
        $this->assertSame('Europe/Warsaw', date_default_timezone_get());
    }

    /**
     * @return void
     */
    public function testDatabaseSessionUsesApplicationTimezone(): void
    {
        $this->assertSame(config('app.timezone'), DB::selectOne('show timezone')->TimeZone);
    }

    /**
     * @return void
     */
    public function testDatabaseDefaultTimestampsMatchApplicationClock(): void
    {
        $calendar = Calendar::factory()->create();
        $calendar->users()->attach(User::factory()->create()->uuid);

        $assignedAt = Carbon::parse(DB::table('calendar_users')->where('calendar_uuid', $calendar->uuid)->value('assigned_at'));

        $this->assertLessThan(60, abs($assignedAt->diffInSeconds(Carbon::now())));
    }

    /**
     * @return void
     */
    public function testModelDatesAreSerializedAsStoredWithoutTimezoneConversion(): void
    {
        $calendar = Calendar::factory()->create(['date' => '2026-10-02']);
        $calendar->created_at = Carbon::parse('2025-06-01 12:00:00');
        $user = User::factory()->make();
        $user->created_at = Carbon::parse('2025-06-01 12:00:00');

        $this->assertSame('2026-10-02T00:00:00.000000', $calendar->fresh()->toArray()['date']);
        $this->assertSame('2025-06-01T12:00:00.000000', $calendar->toArray()['created_at']);
        $this->assertSame('2025-06-01T12:00:00.000000', $user->toArray()['created_at']);
    }

    /**
     * @return void
     */
    public function testApiResponsesReturnDatesAsStoredWithoutTimezoneConversion(): void
    {
        $calendar = Calendar::factory()->create(['date' => '2026-10-02']);
        $calendar->created_at = Carbon::parse('2025-06-01 12:00:00');
        $calendar->save();

        $this->callApiWithLoggedUser()
            ->getJson(route('calendar.show', ['calendar' => $calendar->uuid]))
            ->assertOk()
            ->assertJsonPath('date', '2026-10-02T00:00:00.000000')
            ->assertJsonPath('created_at', '2025-06-01T12:00:00.000000');
    }
}
