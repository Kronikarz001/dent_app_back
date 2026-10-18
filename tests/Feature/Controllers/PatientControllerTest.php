<?php

namespace Tests\Feature\Controllers;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
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
}
