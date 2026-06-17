<?php

namespace Tests\Feature\Services;

use App\Models\JobPosition;
use App\Models\User;
use App\Services\JobPositionServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class JobPositionServiceTest extends TestCase
{
    /**
     * @var JobPositionServiceInterface|Application|mixed|object
     */
    private JobPositionServiceInterface $service;

    protected const JOB_POSITIONS_TABLE = 'job_positions';

    protected const USERS_JOB_POSITIONS_TABLE = 'users_job_positions';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(JobPositionServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetJobPositionsReturnsPaginatedResults(): void
    {
        JobPosition::factory()->count(3)->create();

        $result = $this->service->getJobPositions();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetJobPositionsListReturnsPaginator(): void
    {
        JobPosition::factory()->count(2)->create();

        $result = $this->service->getJobPositionsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateJobPositionPersistsToDatabase(): void
    {
        $data = JobPosition::factory()->make()->toArray();

        $jobPosition = $this->service->createJobPosition($data);

        $this->assertInstanceOf(JobPosition::class, $jobPosition);
        $this->assertDatabaseHas(self::JOB_POSITIONS_TABLE, ['uuid' => $jobPosition->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateJobPositionPersistsChangesToDatabase(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $result = $this->service->updateJobPosition($jobPosition, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(JobPosition::class, $result);
        $this->assertDatabaseHas(self::JOB_POSITIONS_TABLE, ['uuid' => $jobPosition->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteJobPositionRemovesFromDatabase(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $this->service->deleteJobPosition($jobPosition);

        $this->assertDatabaseMissing(self::JOB_POSITIONS_TABLE, ['uuid' => $jobPosition->uuid, 'deleted_at' => null]);
    }

    /**
     * @return void
     */
    public function testAssignJobPositionAttachesPositionToUser(): void
    {
        $user = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $this->service->assignJobPosition($user, ['job_positions' => [$jobPosition->uuid]]);

        $this->assertDatabaseHas(self::USERS_JOB_POSITIONS_TABLE, [
            'user_uuid' => $user->uuid,
            'job_position_uuid' => $jobPosition->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignJobPositionDoesNotDuplicateExistingAssignment(): void
    {
        $user = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $user->jobPositions()->attach($jobPosition->uuid);

        $this->service->assignJobPosition($user, ['job_positions' => [$jobPosition->uuid]]);

        $this->assertSame(1, $user->jobPositions()->count());
    }
}
