<?php

namespace Tests\Unit\Services;

use App\Http\Requests\ExportRequest;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\PatientRepositoryInterface;
use App\Services\ExportServiceInterface;
use App\Services\PatientService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Summary of PatientServiceTest
 */
class PatientServiceTest extends TestCase
{
    private MockInterface $patientRepository;
    private MockInterface $exportService;
    private PatientService $patientService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->patientRepository = Mockery::mock(PatientRepositoryInterface::class);
        $this->exportService     = Mockery::mock(ExportServiceInterface::class);
        $this->patientService    = new PatientService($this->patientRepository, $this->exportService);
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
    public function testGetPatientsDelegatesToRepositoryWithoutColumnFilter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->patientRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->patientService->getPatients();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetPatientsListPassesOnlyUuidAndNameColumns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->patientRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['uuid', 'name'])
            ->andReturn($paginator);

        $result = $this->patientService->getPatientsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testCreatePatientPassesDataUnchangedToRepository(): void
    {
        $data       = ['first_name' => 'Jan', 'last_name' => 'Kowalski', 'email' => 'jan@example.com'];
        $newPatient = Patient::factory()->make();

        $this->patientRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($newPatient);

        $result = $this->patientService->createPatient($data);

        $this->assertInstanceOf(Patient::class, $result);
        $this->assertSame($newPatient, $result);
    }

    /**
     * @return void
     */
    public function testUpdatePatientPassesPatientAndDataToRepository(): void
    {
        $patient        = Patient::factory()->make();
        $data           = ['first_name' => 'Updated'];
        $updatedPatient = Patient::factory()->make(['first_name' => 'Updated']);

        $this->patientRepository
            ->shouldReceive('update')
            ->once()
            ->with($patient, $data)
            ->andReturn($updatedPatient);

        $result = $this->patientService->updatePatient($patient, $data);

        $this->assertInstanceOf(Patient::class, $result);
        $this->assertSame($updatedPatient, $result);
    }

    /**
     * @return void
     */
    public function testDeletePatientCallsRepositoryDeleteNotUpdate(): void
    {
        $patient = Patient::factory()->make();

        $this->patientRepository
            ->shouldReceive('delete')
            ->once()
            ->with($patient);

        $this->patientRepository->shouldNotReceive('update');

        $this->assertNull($this->patientService->deletePatient($patient));
    }

    /**
     * @return void
     */
    public function testExportDelegatesToExportService(): void
    {
        $paginator = new LengthAwarePaginator(collect(), 0, 15, 1);
        $request   = Mockery::mock(ExportRequest::class);
        $response  = Mockery::mock(BinaryFileResponse::class);

        $this->patientRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $this->exportService
            ->shouldReceive('export')
            ->once()
            ->andReturn($response);

        $result = $this->patientService->export($request);

        $this->assertSame($response, $result);
    }
}
