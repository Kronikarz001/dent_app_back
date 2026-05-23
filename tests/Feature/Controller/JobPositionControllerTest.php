<?php

namespace Tests\Feature\Controller;

use App\Models\JobPosition;
use Tests\TestCase;

/**
 * Summary of UserControllerTest
 */
class JobPositionControllerTest extends TestCase
{
    public function test_index_users_return_success_response(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index'));

        $response->assertOk();
    }

    public function test_index_users_list_return_success_response(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.selectList'));

        $response->assertOk();
    }

    public function test_show_job_position_return_success_response(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.show', ['job_position' => $jobPosition->uuid]));

        $response->assertOk();
    }

    public function test_store_job_position_return_created_response(): void
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

    public function test_update_job_position_return_no_content_response(): void
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

    public function test_delete_job_position_return_no_content_response(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('jobPosition.destroy', ['job_position' => $jobPosition->uuid]))
            ->assertNoContent();

        $this->assertSoftDeleted('job_positions', ['uuid' => $jobPosition->uuid]);
    }
}
