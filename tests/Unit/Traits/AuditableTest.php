<?php

namespace Tests\Unit\Traits;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AuditableTest
 */
class AuditableTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatingWithoutAuthenticatedUserDoesNotRecordAudit(): void
    {
        Patient::factory()->create();

        $this->assertSame(0, Audit::count());
    }

    /**
     * @return void
     */
    public function testCreatingWithAuthenticatedUserRecordsAudit(): void
    {
        $actor = User::factory()->create();
        Auth::setUser($actor);

        $patient = Patient::factory()->create(['first_name' => 'Jan']);

        $audit = Audit::query()->where('auditable_id', $patient->uuid)->first();

        $this->assertNotNull($audit);
        $this->assertSame(AuditableEventType::CREATE, $audit->type);
        $this->assertSame(Patient::class, $audit->auditable_type);
        $this->assertSame($actor->uuid, $audit->user_uuid);
        $this->assertNull($audit->change_from);
        $this->assertSame('Jan', $audit->change_to['first_name']);
    }

    /**
     * @return void
     */
    public function testUpdatingWithAuthenticatedUserRecordsOnlyChangedFields(): void
    {
        $actor = User::factory()->create();
        $patient = Patient::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);

        Auth::setUser($actor);
        $patient->update(['first_name' => 'Adam']);

        $audit = Audit::query()
            ->where('auditable_id', $patient->uuid)
            ->where('type', AuditableEventType::UPDATE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame(['first_name' => 'Jan'], $audit->change_from);
        $this->assertSame(['first_name' => 'Adam'], $audit->change_to);
    }

    /**
     * @return void
     */
    public function testUpdatingWithoutAuthenticatedUserDoesNotRecordAudit(): void
    {
        $patient = Patient::factory()->create();

        $patient->update(['first_name' => 'Adam']);

        $this->assertSame(0, Audit::count());
    }

    /**
     * @return void
     */
    public function testTouchingOnlyTimestampDoesNotRecordAudit(): void
    {
        $actor = User::factory()->create();
        $patient = Patient::factory()->create();
        Auth::setUser($actor);

        $patient->touch();

        $this->assertSame(0, Audit::count());
    }

    /**
     * @return void
     */
    public function testDeletingWithAuthenticatedUserRecordsFullSnapshot(): void
    {
        $actor = User::factory()->create();
        $patient = Patient::factory()->create(['first_name' => 'Jan']);
        Auth::setUser($actor);

        $patient->delete();

        $audit = Audit::query()
            ->where('auditable_id', $patient->uuid)
            ->where('type', AuditableEventType::DELETE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('Jan', $audit->change_from['first_name']);
        $this->assertNull($audit->change_to);
    }

    /**
     * @return void
     */
    public function testHiddenAttributesAreExcludedFromSnapshot(): void
    {
        $actor = User::factory()->create();
        Auth::setUser($actor);

        $user = User::factory()->create();

        $audit = Audit::query()
            ->where('auditable_id', $user->uuid)
            ->where('type', AuditableEventType::CREATE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertArrayNotHasKey('password', $audit->change_to);
        $this->assertArrayNotHasKey('pesel', $audit->change_to);
        $this->assertArrayNotHasKey('remember_token', $audit->change_to);
    }
}
