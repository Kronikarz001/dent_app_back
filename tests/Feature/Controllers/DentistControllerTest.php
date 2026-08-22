<?php

namespace Tests\Feature\Controllers;

use App\Models\JobPosition;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of DentistControllerTest
 */
class DentistControllerTest extends TestCase
{
    /**
     * @return JobPosition
     */
    private function dentistJobPosition(): JobPosition
    {
        return JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
    }

    /**
     * @return void
     */
    public function testIndexReturnsOnlyUsersWithDentistJobPosition(): void
    {
        $dentistPosition = $this->dentistJobPosition();
        $otherPosition = JobPosition::factory()->create();

        $dentist = User::factory()->create();
        $dentist->jobPositions()->attach($dentistPosition->uuid);

        $other = User::factory()->create();
        $other->jobPositions()->attach($otherPosition->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentist.index'));

        $response->assertOk();
        $uuids = collect($response->json('data'))->pluck('uuid')->all();
        $this->assertContains($dentist->uuid, $uuids);
        $this->assertNotContains($other->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testSelectListReturnsSuccessResponse(): void
    {
        $dentistPosition = $this->dentistJobPosition();
        $dentist = User::factory()->create();
        $dentist->jobPositions()->attach($dentistPosition->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentist.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowReturnsDentist(): void
    {
        $dentistPosition = $this->dentistJobPosition();
        $dentist = User::factory()->create();
        $dentist->jobPositions()->attach($dentistPosition->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentist.show', ['dentist' => $dentist->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $dentist->uuid);
    }

    /**
     * @return void
     */
    public function testShowReturnsNotFoundForUserWithoutDentistJobPosition(): void
    {
        $otherPosition = JobPosition::factory()->create();
        $user = User::factory()->create();
        $user->jobPositions()->attach($otherPosition->uuid);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentist.show', ['dentist' => $user->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testShowReturnsNotFoundForUserWithoutAnyJobPosition(): void
    {
        $this->dentistJobPosition();
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentist.show', ['dentist' => $user->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testIndexRequiresAuthentication(): void
    {
        $response = $this->getJson(route('dentist.index'));

        $response->assertUnauthorized();
    }
}
