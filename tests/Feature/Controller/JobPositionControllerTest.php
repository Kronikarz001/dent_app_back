<?php

namespace Tests\Feature\Controller;

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
    public function test_index_users_return_success_response(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function test_index_users_list_return_success_response(): void
    {
        JobPosition::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function test_show_job_position_return_success_response(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.show', ['job_position' => $jobPosition->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function test_delete_job_position_return_no_content_response(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('jobPosition.destroy', ['job_position' => $jobPosition->uuid]))
            ->assertNoContent();

        $this->assertSoftDeleted('job_positions', ['uuid' => $jobPosition->uuid]);
    }

    /**
     * @return void
     */
    public function testExportJobPositionReturnSuccessResponse(): void
    {
        JobPosition::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.export', ['type' => 'xlsx']));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testAssignJobPositionReturnNoContentResponse(): void
    {
        $user        = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user.jobPosition.assignJobPosition', ['user' => $user->uuid]), [
                'job_positions' => [$jobPosition->uuid],
            ])
            ->assertNoContent();
    }
}
