<?php

namespace Tests\Unit\Services;

use App\Http\Requests\ExportRequest;
use App\Models\DentalExamination;
use App\Repositories\DentalExaminationRepositoryInterface;
use App\Services\AuditServiceInterface;
use App\Services\DentalExaminationService;
use App\Services\ExportServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Summary of DentalExaminationServiceTest
 */
class DentalExaminationServiceTest extends TestCase
{
    private MockInterface $dentalExaminationRepository;

    private MockInterface $exportService;

    private DentalExaminationService $dentalExaminationService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dentalExaminationRepository = Mockery::mock(DentalExaminationRepositoryInterface::class);
        $this->exportService = Mockery::mock(ExportServiceInterface::class);
        $this->dentalExaminationService = new DentalExaminationService($this->dentalExaminationRepository, $this->exportService, Mockery::mock(AuditServiceInterface::class));
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
    public function testGetDentalExaminationsDelegatesToRepositoryWithoutColumnFilter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->dentalExaminationRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->dentalExaminationService->getDentalExaminations();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetDentalExaminationsListPassesOnlyUuidAndNameColumns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->dentalExaminationRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['uuid', 'name'])
            ->andReturn($paginator);

        $result = $this->dentalExaminationService->getDentalExaminationsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testCreateDentalExaminationPassesDataUnchangedToRepository(): void
    {
        $data = ['name' => 'Przegląd', 'price' => 150];
        $newDentalExamination = DentalExamination::factory()->make();

        $this->dentalExaminationRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($newDentalExamination);

        $result = $this->dentalExaminationService->createDentalExamination($data);

        $this->assertInstanceOf(DentalExamination::class, $result);
        $this->assertSame($newDentalExamination, $result);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationPassesModelAndDataToRepository(): void
    {
        $dentalExamination = DentalExamination::factory()->make();
        $data = ['name' => 'Updated'];
        $updated = DentalExamination::factory()->make(['name' => 'Updated']);

        $this->dentalExaminationRepository
            ->shouldReceive('update')
            ->once()
            ->with($dentalExamination, $data)
            ->andReturn($updated);

        $result = $this->dentalExaminationService->updateDentalExamination($dentalExamination, $data);

        $this->assertInstanceOf(DentalExamination::class, $result);
        $this->assertSame($updated, $result);
    }

    /**
     * @return void
     */
    public function testDeleteDentalExaminationCallsRepositoryDeleteNotUpdate(): void
    {
        $dentalExamination = DentalExamination::factory()->make();

        $this->dentalExaminationRepository
            ->shouldReceive('delete')
            ->once()
            ->with($dentalExamination);

        $this->dentalExaminationRepository->shouldNotReceive('update');

        $this->assertNull($this->dentalExaminationService->deleteDentalExamination($dentalExamination));
    }

    /**
     * @return void
     */
    public function testExportDelegatesToExportService(): void
    {
        $paginator = new LengthAwarePaginator(collect(), 0, 15, 1);
        $request = Mockery::mock(ExportRequest::class);
        $response = Mockery::mock(BinaryFileResponse::class);

        $this->dentalExaminationRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $this->exportService
            ->shouldReceive('export')
            ->once()
            ->andReturn($response);

        $result = $this->dentalExaminationService->export($request);

        $this->assertSame($response, $result);
    }
}
