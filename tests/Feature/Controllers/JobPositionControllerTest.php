<?php

namespace Tests\Feature\Controllers;

use App\Models\JobPosition;
use Tests\TestCase;

/**
 * Summary of UserControllerTest
 */
class JobPositionControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexUsersReturnSuccessResponse(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexUsersListReturnSuccessResponse(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowJobPositionReturnSuccessResponse(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.show', ['job_position' => $jobPosition->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStoreJobPositionReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('jobPosition.store'), [
                'name' => 'Test',
                'f_name' => 'User',
                'm_name' => 'example@mail',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('job_positions', [
            'm_name' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateJobPositionReturnNoContentResponse(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('jobPosition.update', ['job_position' => $jobPosition->uuid]), [
                'name' => 'Test',
                'f_name' => 'User',
                'm_name' => 'example@mail',
            ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('job_positions', [
            'name' => 'Test',
            'f_name' => 'User',
            'm_name' => 'example@mail',
        ]);
    }

    /**
     * @return void
     */
    public function testDeleteJobPositionReturnNoContentResponse(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('jobPosition.destroy', ['job_position' => $jobPosition->uuid]))
            ->assertNoContent();

        $this->assertSoftDeleted('job_positions', ['uuid' => $jobPosition->uuid]);
    }
}
