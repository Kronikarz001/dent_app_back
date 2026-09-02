<?php

namespace Tests\Feature\Controllers;

use App\Models\JobPosition;
use App\Models\Permission;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of JobPositionControllerTest
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
    public function testIndexFiltersByExactFieldValue(): void
    {
        JobPosition::factory()->create(['name' => 'Zbigniew']);
        $target = JobPosition::factory()->create(['name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index', ['jobPosition' => ['name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        JobPosition::factory()->create(['name' => 'Bartosz']);
        JobPosition::factory()->create(['name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index', ['sort' => 'name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = JobPosition::factory()->create(['name' => 'Wyjatkowy']);
        JobPosition::factory()->create(['name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobPosition.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
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

    /**
     * @return void
     */
    public function testAssignJobPositionReturnNoContentResponse(): void
    {
        $user = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('user.jobPosition.assignJobPosition', ['user' => $user->uuid]), [
                'job_position_uuid' => $jobPosition->uuid,
            ])
            ->assertNoContent();

        $this->assertSame($jobPosition->uuid, $user->fresh()->job_position_uuid);
    }

    /**
     * @return void
     */
    public function testAssignPermissionsCreatesDirectGrant(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $permission = Permission::factory()->create();

        $this->callApiWithLoggedUser()
            ->patchJson(route('jobPosition.assignPermissions', ['jobPosition' => $jobPosition->uuid]), [
                'permissions' => [$permission->uuid],
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('permission_assignments', [
            'grantable_type' => Permission::class,
            'grantable_id' => $permission->uuid,
            'assignable_type' => JobPosition::class,
            'assignable_id' => $jobPosition->uuid,
        ]);
    }
}
