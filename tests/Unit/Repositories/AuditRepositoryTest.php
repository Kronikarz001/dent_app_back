<?php

namespace Tests\Unit\Repositories;

use App\Exceptions\InvalidAuditableIdentifierException;
use App\Models\Patient;
use App\Repositories\AuditRepositoryInterface;
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
