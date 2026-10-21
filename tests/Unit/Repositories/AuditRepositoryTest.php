<?php

namespace Tests\Unit\Repositories;

use App\Enums\AuditableEventType;
use App\Exceptions\InvalidAuditableIdentifierException;
use App\Models\Audit;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\AuditRepositoryInterface;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Summary of AuditRepositoryTest
 */
class AuditRepositoryTest extends TestCase
{
    /**
     * @var AuditRepositoryInterface
     */
    private AuditRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AuditRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testFindLatestChangeToReturnsNewestMatchingAudit(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create();
        $this->createAudit($patient, ['doctor_uuid' => $doctor->uuid], '2021-03-15 10:00:00');
        $expected = $this->createAudit($patient, ['doctor_uuid' => $doctor->uuid], '2023-05-01 10:00:00');
        $this->createAudit($patient, ['doctor_uuid' => User::factory()->create()->uuid], '2024-01-01 10:00:00');
        $this->createAudit(Patient::factory()->create(), ['doctor_uuid' => $doctor->uuid], '2025-01-01 10:00:00');

        $found = $this->repository->findLatestChangeTo($patient, 'doctor_uuid', $doctor->uuid);

        $this->assertTrue($found->is($expected));
    }

    /**
     * @return void
     */
    public function testFindLatestChangeToReturnsNullWithoutMatch(): void
    {
        $patient = Patient::factory()->create();

        $this->assertNull($this->repository->findLatestChangeTo($patient, 'doctor_uuid', User::factory()->create()->uuid));
    }

    /**
     * @param Patient $patient
     * @param array $changeTo
     * @param string $createdAt
     * @return Audit
     */
    private function createAudit(Patient $patient, array $changeTo, string $createdAt): Audit
    {
        $audit = Audit::create([
            'auditable_type' => $patient->getMorphClass(),
            'auditable_id' => $patient->uuid,
            'user_uuid' => User::factory()->create()->uuid,
            'type' => AuditableEventType::UPDATE,
            'change_to' => $changeTo,
        ]);
        $audit->created_at = Carbon::parse($createdAt);
        $audit->save();

        return $audit;
    }

    /**
     * @return void
     */
    public function testFindAuditableOrFailReturnsMatchingModel(): void
    {
        $patient = Patient::factory()->create();

        $found = $this->repository->findAuditableOrFail(Patient::class, $patient->uuid);

        $this->assertTrue($found->is($patient));
    }

    /**
     * @return void
     */
    public function testFindAuditableOrFailThrowsForInvalidIdentifier(): void
    {
        $this->expectException(InvalidAuditableIdentifierException::class);

        $this->repository->findAuditableOrFail(Patient::class, 'not-a-valid-uuid');
    }
}
