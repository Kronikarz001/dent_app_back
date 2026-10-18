<?php

namespace Tests\Unit\Models;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Patient;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AuditTest
 */
final class AuditTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testTypeChangeFromAndChangeToAreCast(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $audit = Audit::create([
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->uuid,
            'user_uuid' => $user->uuid,
            'type' => AuditableEventType::UPDATE->value,
            'change_from' => ['first_name' => 'Old'],
            'change_to' => ['first_name' => 'New'],
        ]);

        $this->assertSame(AuditableEventType::UPDATE, $audit->type);
        $this->assertSame(['first_name' => 'Old'], $audit->change_from);
        $this->assertSame(['first_name' => 'New'], $audit->change_to);
    }

    /**
     * @return void
     */
    public function testAuditableReturnsOwningModel(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $audit = Audit::create([
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->uuid,
            'user_uuid' => $user->uuid,
            'type' => AuditableEventType::CREATE->value,
        ]);

        $this->assertTrue($audit->auditable->is($patient));
    }

    /**
     * @return void
     */
    public function testUserReturnsActingUser(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $audit = Audit::create([
            'auditable_type' => Patient::class,
            'auditable_id' => $patient->uuid,
            'user_uuid' => $user->uuid,
            'type' => AuditableEventType::CREATE->value,
        ]);

        $this->assertTrue($audit->user->is($user));
    }
}
