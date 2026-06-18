<?php

namespace Tests\Feature\Controllers;

use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of AuditableControllerTest
 */
class AuditableControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testHistoryReturnsAuditEntriesForResource(): void
    {
        $actor = User::factory()->create();
        $patient = Patient::factory()->create(['first_name' => 'Jan']);

        $this->callApiWithLoggedUser($actor)
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => 'Adam',
                'last_name' => $patient->last_name,
                'email' => $patient->email,
            ]);

        $response = $this->callApiWithLoggedUser($actor)
            ->getJson(route('auditable.history', ['resource' => 'patient', 'uuid' => $patient->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.auditable_id', $patient->uuid);
    }

    /**
     * @return void
     */
    public function testHistoryReturnsNotFoundForUnknownResource(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('auditable.history', ['resource' => 'unknown', 'uuid' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testHistoryReturnsNotFoundForMissingEntity(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('auditable.history', ['resource' => 'patient', 'uuid' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testHistoryRequiresAuthentication(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->getJson(route('auditable.history', ['resource' => 'patient', 'uuid' => $patient->uuid]));

        $response->assertUnauthorized();
    }

    /**
     * @return void
     */
    public function testHistoryExportReturnsSuccessResponse(): void
    {
        $actor = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->callApiWithLoggedUser($actor)
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => 'Adam',
                'last_name' => $patient->last_name,
                'email' => $patient->email,
            ]);

        $response = $this->callApiWithLoggedUser($actor)
            ->getJson(route('auditable.history.export', ['resource' => 'patient', 'uuid' => $patient->uuid, 'type' => 'xlsx']));

        $response->assertOk();
    }
}
