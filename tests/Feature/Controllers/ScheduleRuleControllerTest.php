<?php

namespace Tests\Feature\Controllers;

use App\Models\ScheduleRule;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of ScheduleRuleControllerTest
 */
class ScheduleRuleControllerTest extends TestCase
{
    /**
     * @return User
     */
    private function loggedUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return void
     */
    public function testIndexReturnsOnlyRulesOfLoggedUser(): void
    {
        $user = $this->loggedUser();
        $mine = ScheduleRule::factory()->create(['user_uuid' => $user->uuid, 'day' => 'monday']);
        ScheduleRule::factory()->create();

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('schedule-rule.index'));

        $response->assertOk();
        $this->assertSame([$mine->uuid], collect($response->json('data'))->pluck('uuid')->all());
        $response->assertJsonPath('data.0.day', 'monday');
    }

    /**
     * @return void
     */
    public function testStoreAssignsRuleToLoggedUser(): void
    {
        $user = $this->loggedUser();
        $other = User::factory()->create();

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('schedule-rule.store'), [
                'day' => 'wednesday',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'user_uuid' => $other->uuid,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('user_uuid', $user->uuid);
        $this->assertDatabaseHas('schedule_rules', ['user_uuid' => $user->uuid, 'day' => 'wednesday']);
        $this->assertDatabaseMissing('schedule_rules', ['user_uuid' => $other->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreValidatesDayAndTimes(): void
    {
        $this->callApiWithLoggedUser()
            ->postJson(route('schedule-rule.store'), [
                'day' => 'someday',
                'start_time' => '18:00',
                'end_time' => '10:00',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['day', 'end_time']);
    }

    /**
     * @return void
     */
    public function testShowReturnsOwnRule(): void
    {
        $user = $this->loggedUser();
        $rule = ScheduleRule::factory()->create(['user_uuid' => $user->uuid]);

        $this->callApiWithLoggedUser($user)
            ->getJson(route('schedule-rule.show', ['scheduleRule' => $rule->uuid]))
            ->assertOk()
            ->assertJsonPath('uuid', $rule->uuid);
    }

    /**
     * @return void
     */
    public function testUpdateChangesOwnRule(): void
    {
        $user = $this->loggedUser();
        $rule = ScheduleRule::factory()->create(['user_uuid' => $user->uuid, 'day' => 'monday']);

        $this->callApiWithLoggedUser($user)
            ->putJson(route('schedule-rule.update', ['scheduleRule' => $rule->uuid]), [
                'day' => 'friday',
                'start_time' => '09:00',
                'end_time' => '15:00',
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('schedule_rules', ['uuid' => $rule->uuid, 'day' => 'friday']);
    }

    /**
     * @return void
     */
    public function testRuleOfAnotherUserIsNotAccessible(): void
    {
        $rule = ScheduleRule::factory()->create(['day' => 'monday']);
        $client = $this->callApiWithLoggedUser($this->loggedUser());

        $client->getJson(route('schedule-rule.show', ['scheduleRule' => $rule->uuid]))->assertNotFound();
        $client->putJson(route('schedule-rule.update', ['scheduleRule' => $rule->uuid]), [
            'day' => 'friday',
            'start_time' => '09:00',
            'end_time' => '15:00',
        ])->assertNotFound();
        $client->deleteJson(route('schedule-rule.destroy', ['scheduleRule' => $rule->uuid]))->assertNotFound();

        $this->assertDatabaseHas('schedule_rules', ['uuid' => $rule->uuid, 'day' => 'monday']);
    }

    /**
     * @return void
     */
    public function testDestroyDeletesOwnRule(): void
    {
        $user = $this->loggedUser();
        $rule = ScheduleRule::factory()->create(['user_uuid' => $user->uuid]);

        $this->callApiWithLoggedUser($user)
            ->deleteJson(route('schedule-rule.destroy', ['scheduleRule' => $rule->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('schedule_rules', ['uuid' => $rule->uuid]);
    }
}
