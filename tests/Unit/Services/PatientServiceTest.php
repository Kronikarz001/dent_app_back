<?php

namespace Tests\Unit\Services;

use App\Dto\PatientVisitDto;
use App\Http\Requests\ExportRequest;
use App\Mappers\PatientVisitMapper;
use App\Models\Audit;
use App\Models\Calendar;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\AuditRepositoryInterface;
use App\Repositories\CalendarRepositoryInterface;
use App\Repositories\PatientRepositoryInterface;
use App\Services\ExportServiceInterface;
use App\Services\PatientService;
use App\Services\PhoneNumberServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
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

    private MockInterface $calendarRepository;

    private MockInterface $auditRepository;

    private PatientService $patientService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->patientRepository = Mockery::mock(PatientRepositoryInterface::class);
        $this->exportService = Mockery::mock(ExportServiceInterface::class);
        $phoneNumberService = Mockery::mock(PhoneNumberServiceInterface::class);
        $this->calendarRepository = Mockery::mock(CalendarRepositoryInterface::class);
        $this->auditRepository = Mockery::mock(AuditRepositoryInterface::class);
        $this->patientService = new PatientService(
            $this->patientRepository,
            $this->exportService,
            $phoneNumberService,
            $this->calendarRepository,
            $this->auditRepository,
            new PatientVisitMapper
        );
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
    public function testGetPatientsListSelectsOnlyUuidAndNameColumns(): void
    {
        $collection = new Collection;

        $this->patientRepository
            ->shouldReceive('findSelectAll')
            ->once()
            ->with(['uuid', 'first_name', 'last_name'])
            ->andReturn($collection);

        $result = $this->patientService->getPatientsList();

        $this->assertSame($collection, $result);
    }

    /**
     * @return void
     */
    public function testCreatePatientPassesDataUnchangedToRepository(): void
    {
        $data = ['first_name' => 'Jan', 'last_name' => 'Kowalski', 'email' => 'jan@example.com'];
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
        $patient = Patient::factory()->make();
        $data = ['first_name' => 'Updated'];
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
        $request = Mockery::mock(ExportRequest::class);
        $response = Mockery::mock(BinaryFileResponse::class);

        $this->patientRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with([], -1)
            ->andReturn($paginator);

        $this->exportService
            ->shouldReceive('export')
            ->once()
            ->andReturn($response);

        $result = $this->patientService->export($request);

        $this->assertSame($response, $result);
    }

    /**
     * @return void
     */
    public function testGetStatisticsAggregatesRepositoryResults(): void
    {
        $patient = Patient::factory()->create();
        $lastVisit = Calendar::factory()->create(['date' => '2026-10-02', 'start_time' => '12:00:00']);
        $nextVisit = Calendar::factory()->create(['date' => '2026-10-28', 'start_time' => '10:30:00']);
        $lastNoShow = Calendar::factory()->create(['date' => '2026-08-14', 'start_time' => '09:00:00']);

        $this->patientRepository->shouldReceive('findDoctor')->once()->with($patient)->andReturnNull();
        $this->calendarRepository->shouldReceive('findLastVisitForPatient')->once()->with($patient)->andReturn($lastVisit);
        $this->calendarRepository->shouldReceive('findNextVisitForPatient')->once()->with($patient)->andReturn($nextVisit);
        $this->calendarRepository->shouldReceive('countForPatient')->once()->with($patient, true)->andReturn(2);
        $this->calendarRepository->shouldReceive('countForPatient')->once()->with($patient)->andReturn(10);
        $this->calendarRepository->shouldReceive('findLastNoShowForPatient')->once()->with($patient)->andReturn($lastNoShow);
        $this->auditRepository->shouldNotReceive('findLatestChangeTo');

        $result = $this->patientService->getStatistics($patient);

        $this->assertSame($lastVisit->uuid, $result->getLastVisit()->getUuid());
        $this->assertSame($nextVisit->uuid, $result->getNextVisit()->getUuid());
        $this->assertSame(2, $result->getNoShowCount());
        $this->assertSame(10, $result->getTotalAppointments());
        $this->assertSame('2026-08-14 09:00:00', $result->getLastNoShowAt()->toDateTimeString());
        $this->assertNull($result->getLeadDoctor());
        $this->assertNull($result->getLeadDoctorSince());
    }

    /**
     * @return void
     */
    public function testGetStatisticsResolvesLeadDoctorSinceFromAudit(): void
    {
        $doctor = User::factory()->create();
        $patient = Patient::factory()->create(['doctor_uuid' => $doctor->uuid]);
        $audit = new Audit(['change_to' => ['doctor_uuid' => $doctor->uuid]]);
        $audit->created_at = Carbon::parse('2021-03-15 10:00:00');

        $this->patientRepository->shouldReceive('findDoctor')->once()->with($patient)->andReturn($doctor);
        $this->calendarRepository->shouldReceive('findLastVisitForPatient')->andReturnNull();
        $this->calendarRepository->shouldReceive('findNextVisitForPatient')->andReturnNull();
        $this->calendarRepository->shouldReceive('countForPatient')->andReturn(0);
        $this->calendarRepository->shouldReceive('findLastNoShowForPatient')->andReturnNull();
        $this->auditRepository
            ->shouldReceive('findLatestChangeTo')
            ->once()
            ->with($patient, 'doctor_uuid', $doctor->uuid)
            ->andReturn($audit);

        $result = $this->patientService->getStatistics($patient);

        $this->assertTrue($result->getLeadDoctor()->is($doctor));
        $this->assertSame('2021-03-15', $result->getLeadDoctorSince());
    }

    /**
     * @return void
     */
    public function testGetVisitsMapsRepositoryCalendarsToVisitDtos(): void
    {
        $patient = Patient::factory()->create();
        $calendar = Calendar::factory()->create(['date' => '2026-10-02', 'start_time' => '12:00:00']);
        $paginator = new LengthAwarePaginator([$calendar], 1, 15);

        $this->calendarRepository->shouldReceive('findVisitsForPatient')->once()->with($patient)->andReturn($paginator);

        $result = $this->patientService->getVisits($patient);

        $this->assertSame(1, $result->total());
        $this->assertInstanceOf(PatientVisitDto::class, $result->getCollection()->first());
        $this->assertSame($calendar->uuid, $result->getCollection()->first()->getUuid());
    }
}
