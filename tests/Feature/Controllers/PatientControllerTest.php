<?php

namespace Tests\Feature\Controllers;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of PatientControllerTest
 */
class PatientControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexPatientsReturnSuccessResponse(): void
    {
        Patient::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexPatientsListReturnSuccessResponse(): void
    {
        Patient::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexFiltersByExactFieldValue(): void
    {
        Patient::factory()->create(['first_name' => 'Zbigniew']);
        $target = Patient::factory()->create(['first_name' => 'Unikalniejszy']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.index', ['patient' => ['first_name' => 'Unikalniejszy']]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testIndexSortsResultsByField(): void
    {
        Patient::factory()->create(['first_name' => 'Bartosz']);
        Patient::factory()->create(['first_name' => 'Adam']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.index', ['sort' => 'first_name,asc']));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('first_name')->all();
        $this->assertSame(collect($names)->sort()->values()->all(), $names);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringMatchesPartialValue(): void
    {
        $target = Patient::factory()->create(['first_name' => 'Wyjatkowy']);
        Patient::factory()->create(['first_name' => 'Inny']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.index', ['searchString' => 'Wyjatkowy']));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $target->uuid);
    }

    /**
     * @return void
     */
    public function testRomexisSyncListReturnsOnlyPatientsWithRomexisIdSet(): void
    {
        $withId = Patient::factory()->create(['romexis_patient_id' => '12345']);
        Patient::factory()->create(['romexis_patient_id' => null]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.romexisSyncList'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $withId->uuid);
        $response->assertJsonPath('data.0.romexis_patient_id', '12345');
        $this->assertArrayNotHasKey('first_name', $response->json('data.0'));
    }

    /**
     * @return void
     */
    public function testUserGrantedOnlyPatientphotoPermissionCanUseRomexisSyncListButNotPatientIndex(): void
    {
        $serviceAccount = User::factory()->create(['is_admin' => false]);

        foreach (['view', 'edit'] as $type) {
            $permission = Permission::where(['resource' => 'patientphoto', 'type' => $type])->firstOrFail();
            PermissionAssignment::create([
                'grantable_type' => Permission::class,
                'grantable_id' => $permission->uuid,
                'assignable_type' => User::class,
                'assignable_id' => $serviceAccount->uuid,
            ]);
        }

        $this->callApiWithLoggedUser($serviceAccount)
            ->getJson(route('patient.romexisSyncList'))
            ->assertOk();

        $this->callApiWithLoggedUser($serviceAccount)
            ->getJson(route('patient.index'))
            ->assertForbidden();
    }

    /**
     * @return void
     */
    public function testStorePatientWithDuplicateRomexisPatientIdReturnsValidationError(): void
    {
        Patient::factory()->create(['romexis_patient_id' => '12345']);

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'inny@example.com',
                'pesel' => '44051401359',
                'romexis_patient_id' => '12345',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdatePatientSetsRomexisPatientId(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email,
                'romexis_patient_id' => '98765',
            ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('patients', [
            'uuid' => $patient->uuid,
            'romexis_patient_id' => '98765',
        ]);
    }

    /**
     * @return void
     */
    public function testShowPatientReturnSuccessResponse(): void
    {
        $patient = Patient::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.show', ['patient' => $patient->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStorePatientReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'jan@example.com',
                'pesel' => '44051401359',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('patients', [
            'email' => 'jan@example.com',
        ]);
    }

    /**
     * @return void
     */
    public function testStorePatientRecordsAuditAttributedToActingUser(): void
    {
        $actor = User::factory()->create();

        $response = $this->callApiWithLoggedUser($actor)
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'jan@example.com',
                'pesel' => '44051401359',
            ]);

        $response->assertCreated();

        $patient = Patient::query()->where('email', 'jan@example.com')->first();
        $audit = Audit::query()->where('auditable_id', $patient->uuid)->first();

        $this->assertNotNull($audit);
        $this->assertSame(Patient::class, $audit->auditable_type);
        $this->assertSame(AuditableEventType::CREATE, $audit->type);
        $this->assertSame($actor->uuid, $audit->user_uuid);
        $this->assertSame('jan@example.com', $audit->change_to['email']);
    }

    /**
     * @return void
     */
    public function testStorePatientWithDuplicateEmailReturnsValidationError(): void
    {
        Patient::factory()->create(['email' => 'jan@example.com']);

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'jan@example.com',
                'pesel' => '99010112342',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testStorePatientWithDuplicatePeselReturnsValidationError(): void
    {
        Patient::factory()->create(['pesel' => '44051401359']);

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'inny@example.com',
                'pesel' => '44051401359',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testStorePatientWithMissingRequiredFieldsReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdatePatientAssignsPhoneNumbers(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email,
                'phone_numbers' => [
                    ['number' => '48500100200', 'type' => 'PRIVATE'],
                ],
            ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_type' => Patient::class,
            'phoneable_uuid' => $patient->uuid,
            'number' => '48500100200',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePatientWithInvalidPhoneNumberReturnsValidationError(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => $patient->email,
                'phone_numbers' => [
                    ['number' => 'not-a-phone-number', 'type' => 'PRIVATE'],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdatePatientWithDuplicateEmailReturnsValidationError(): void
    {
        Patient::factory()->create(['email' => 'taken@example.com']);
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'email' => 'taken@example.com',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdatePatientReturnNoContentResponse(): void
    {
        $patient = Patient::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => 'Updated',
                'last_name' => 'Kowalski',
                'email' => 'updated@example.com',
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Updated',
            'last_name' => 'Kowalski',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletePatientReturnNoContentResponse(): void
    {
        $patient = Patient::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('patient.destroy', ['patient' => $patient->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('patients', ['uuid' => $patient->uuid]);
    }

    /**
     * @return void
     */
    public function testStatisticsReturnsVisitsNoShowsAndLeadDoctor(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $jobPosition = JobPosition::factory()->create(['name' => 'Lekarz dentysta']);
        $leadDoctor = User::factory()->create(['job_position_uuid' => $jobPosition->uuid]);
        $visitDoctor = User::factory()->create(['job_position_uuid' => $jobPosition->uuid]);
        $patient = Patient::factory()->create(['doctor_uuid' => $leadDoctor->uuid]);

        $lastVisit = $this->createVisitFor($patient, '2026-10-02', '12:00', false, $visitDoctor);
        $nextVisit = $this->createVisitFor($patient, '2026-10-28', '10:30', false, $visitDoctor);
        $this->createVisitFor($patient, '2026-08-14', '09:00', true);

        Audit::create([
            'auditable_type' => $patient->getMorphClass(),
            'auditable_id' => $patient->uuid,
            'user_uuid' => $leadDoctor->uuid,
            'type' => AuditableEventType::UPDATE,
            'change_to' => ['doctor_uuid' => $leadDoctor->uuid],
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.statistics', ['patient' => $patient->uuid]));

        $response->assertOk()
            ->assertJsonPath('last_visit.uuid', $lastVisit->uuid)
            ->assertJsonPath('last_visit.starts_at', '2026-10-02T12:00:00.000000')
            ->assertJsonPath('last_visit.doctor', [
                'uuid' => $visitDoctor->uuid,
                'first_name' => $visitDoctor->first_name,
                'last_name' => $visitDoctor->last_name,
                'position' => 'Lekarz dentysta',
            ])
            ->assertJsonPath('next_visit.uuid', $nextVisit->uuid)
            ->assertJsonPath('next_visit.starts_at', '2026-10-28T10:30:00.000000')
            ->assertJsonPath('no_shows.count', 1)
            ->assertJsonPath('no_shows.total_appointments', 3)
            ->assertJsonPath('no_shows.last_at', '2026-08-14T09:00:00.000000')
            ->assertJsonPath('lead_doctor.doctor.uuid', $leadDoctor->uuid)
            ->assertJsonPath('lead_doctor.since', '2026-10-21');
    }

    /**
     * @return void
     */
    public function testStatisticsReturnsEmptyValuesForPatientWithoutHistory(): void
    {
        $patient = Patient::factory()->create(['doctor_uuid' => null]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.statistics', ['patient' => $patient->uuid]));

        $response->assertOk()
            ->assertExactJson([
                'last_visit' => null,
                'next_visit' => null,
                'no_shows' => [
                    'count' => 0,
                    'total_appointments' => 0,
                    'last_at' => null,
                ],
                'lead_doctor' => null,
            ]);
    }

    /**
     * @return void
     */
    public function testStatisticsHandlesVisitWithoutDoctorAndLeadDoctorWithoutHistory(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $leadDoctor = User::factory()->create(['job_position_uuid' => null]);
        $patient = Patient::factory()->create(['doctor_uuid' => $leadDoctor->uuid]);
        $this->createVisitFor($patient, '2026-10-28', '10:30');

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.statistics', ['patient' => $patient->uuid]));

        $response->assertOk()
            ->assertJsonPath('last_visit', null)
            ->assertJsonPath('next_visit.doctor', null)
            ->assertJsonPath('no_shows.count', 0)
            ->assertJsonPath('no_shows.total_appointments', 1)
            ->assertJsonPath('no_shows.last_at', null)
            ->assertJsonPath('lead_doctor.doctor', [
                'uuid' => $leadDoctor->uuid,
                'first_name' => $leadDoctor->first_name,
                'last_name' => $leadDoctor->last_name,
                'position' => null,
            ])
            ->assertJsonPath('lead_doctor.since', null);
    }

    /**
     * @return void
     */
    public function testStatisticsReturnsNotFoundForUnknownPatient(): void
    {
        $this->callApiWithLoggedUser()
            ->getJson(route('patient.statistics', ['patient' => Str::uuid()->toString()]))
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function testVisitsReturnsPaginatedArchiveNewestFirst(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00', 'Europe/Warsaw'));
        $doctor = User::factory()->create();
        $patient = Patient::factory()->create();
        $examination = DentalExamination::factory()->create(['name' => 'Przegląd stomatologiczny']);

        $completed = $this->createVisitFor($patient, '2026-10-02', '12:00', false, $doctor);
        $completed->dentalExaminations()->attach($examination->uuid);
        $noShow = $this->createVisitFor($patient, '2026-08-14', '09:00', true);
        $scheduled = $this->createVisitFor($patient, '2026-10-28', '10:30');
        $this->createVisitFor(Patient::factory()->create(), '2026-10-10', '10:00');

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.visits', ['patient' => $patient->uuid]));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.uuid', $scheduled->uuid)
            ->assertJsonPath('data.0.status', 'SCHEDULED')
            ->assertJsonPath('data.0.treatments', [])
            ->assertJsonPath('data.0.doctor', null)
            ->assertJsonPath('data.1.uuid', $completed->uuid)
            ->assertJsonPath('data.1.treatments.0.uuid', $examination->uuid)
            ->assertJsonPath('data.1.treatments.0.name', 'Przegląd stomatologiczny')
            ->assertJsonPath('data.1.date', '2026-10-02T12:00:00.000000')
            ->assertJsonPath('data.1.doctor', [
                'uuid' => $doctor->uuid,
                'first_name' => $doctor->first_name,
                'last_name' => $doctor->last_name,
                'position' => $doctor->jobPosition?->name,
            ])
            ->assertJsonPath('data.1.status', 'COMPLETED')
            ->assertJsonPath('data.2.uuid', $noShow->uuid)
            ->assertJsonPath('data.2.date', '2026-08-14T09:00:00.000000')
            ->assertJsonPath('data.2.status', 'NO_SHOW');
    }

    /**
     * @return void
     */
    public function testVisitsRespectsSortAndPerPageParameters(): void
    {
        $patient = Patient::factory()->create();
        $oldest = $this->createVisitFor($patient, '2026-01-10', '09:00');
        $this->createVisitFor($patient, '2026-05-10', '09:00');
        $this->createVisitFor($patient, '2026-10-02', '12:00');

        $this->callApiWithLoggedUser()
            ->getJson(route('patient.visits', ['patient' => $patient->uuid, 'sort' => 'date,asc', 'perPage' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.uuid', $oldest->uuid)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);
    }

    /**
     * @return void
     */
    public function testVisitsReturnsEmptyListForPatientWithoutVisits(): void
    {
        $patient = Patient::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('patient.visits', ['patient' => $patient->uuid]))
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }

    /**
     * @return void
     */
    public function testVisitsReturnsNotFoundForUnknownPatient(): void
    {
        $this->callApiWithLoggedUser()
            ->getJson(route('patient.visits', ['patient' => Str::uuid()->toString()]))
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function testVisitsRequiresAuthentication(): void
    {
        $patient = Patient::factory()->create();

        $this->getJson(route('patient.visits', ['patient' => $patient->uuid]))
            ->assertUnauthorized();
    }

    /**
     * @param Patient $patient
     * @param string $date
     * @param string $startTime
     * @param bool $noShow
     * @param User|null $doctor
     * @return Calendar
     */
    private function createVisitFor(Patient $patient, string $date, string $startTime, bool $noShow = false, ?User $doctor = null): Calendar
    {
        $calendar = Calendar::factory()->create([
            'date' => $date,
            'start_time' => $startTime,
            'no_show' => $noShow,
        ]);
        $calendar->patients()->attach($patient->uuid);

        if ($doctor !== null) {
            $calendar->users()->attach($doctor->uuid);
        }

        return $calendar;
    }
}
