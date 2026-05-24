<?php

namespace Tests\Unit\Service;

use App\Http\Requests\ExportRequest;
use App\Models\JobPosition;
use App\Repositories\JobPositionRepositoryInterface;
use App\Services\ExportServiceInterface;
use App\Services\JobPositionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Summary of JobPositionServiceTest
 */
class JobPositionServiceTest extends TestCase
{
    private MockInterface $jobPositionRepository;

    private MockInterface $exportService;

    private JobPositionService $jobPositionService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobPositionRepository = Mockery::mock(JobPositionRepositoryInterface::class);
        $this->exportService = Mockery::mock(ExportServiceInterface::class);
        $this->jobPositionService = new JobPositionService($this->jobPositionRepository, $this->exportService);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_get_job_positions_delegates_to_repository_without_column_filter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->jobPositionRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->jobPositionService->getJobPositions();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function test_get_job_positions_list_passes_only_uuid_and_name_columns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->jobPositionRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['uuid', 'name'])
            ->andReturn($paginator);

        $result = $this->jobPositionService->getJobPositionsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function test_create_job_position_passes_data_unchanged_to_repository(): void
    {
        $data = ['name' => 'Lekarz', 'f_name' => 'Lekarka', 'm_name' => 'Lekarz'];
        $newJobPosition = JobPosition::factory()->make();

        $this->jobPositionRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($newJobPosition);

        $result = $this->jobPositionService->createJobPosition($data);

        $this->assertInstanceOf(JobPosition::class, $result);
        $this->assertSame($newJobPosition, $result);
    }

    /**
     * @return void
     */
    public function test_update_job_position_passes_job_position_and_data_to_repository(): void
    {
        $jobPosition = JobPosition::factory()->make();
        $data = ['name' => 'Updated'];
        $updatedJobPosition = JobPosition::factory()->make(['name' => 'Updated']);

        $this->jobPositionRepository
            ->shouldReceive('update')
            ->once()
            ->with($jobPosition, $data)
            ->andReturn($updatedJobPosition);

        $result = $this->jobPositionService->updateJobPosition($jobPosition, $data);

        $this->assertInstanceOf(JobPosition::class, $result);
        $this->assertSame($updatedJobPosition, $result);
    }

    /**
     * @return void
     */
    public function test_delete_job_position_calls_repository_delete_not_update(): void
    {
        $jobPosition = JobPosition::factory()->make();

        $this->jobPositionRepository
            ->shouldReceive('delete')
            ->once()
            ->with($jobPosition);

        $this->jobPositionRepository->shouldNotReceive('update');

        $this->assertNull($this->jobPositionService->deleteJobPosition($jobPosition));
    }

    /**
     * @return void
     */
    public function test_export_delegates_to_export_service(): void
    {
        $paginator = new LengthAwarePaginator(collect(), 0, 15, 1);
        $request = Mockery::mock(ExportRequest::class);
        $response = Mockery::mock(BinaryFileResponse::class);

        $this->jobPositionRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $this->exportService
            ->shouldReceive('export')
            ->once()
            ->andReturn($response);

        $result = $this->jobPositionService->export($request);

        $this->assertSame($response, $result);
    }
}
