<?php

namespace Tests\Unit\Services;

use App\Enums\AuditableEventType;
use App\Exceptions\InvalidAuditableIdentifierException;
use App\Models\Audit;
use App\Models\User;
use App\Repositories\AuditRepositoryInterface;
use App\Services\AuditService;
use App\Services\ExportServiceInterface;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of AuditServiceTest
 */
class AuditServiceTest extends TestCase
{
    private MockInterface $auditRepository;

    private AuditService $auditService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->auditRepository = Mockery::mock(AuditRepositoryInterface::class);
        $this->auditService = new AuditService($this->auditRepository, Mockery::mock(ExportServiceInterface::class));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testRecordCreatedDoesNothingWhenNotAuthenticated(): void
    {
        $model = User::factory()->make();

        $this->auditRepository->shouldNotReceive('create');

        $this->auditService->recordCreated($model);
    }

    /**
     * @return void
     */
    public function testRecordCreatedPersistsSnapshotWithoutHiddenAttributes(): void
    {
        $actor = User::factory()->make(['uuid' => 'actor-uuid']);
        Auth::setUser($actor);
        $model = User::factory()->make(['uuid' => 'subject-uuid', 'password' => 'secret-hash']);

        $this->auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['auditable_type'] === User::class
                    && $data['auditable_id'] === 'subject-uuid'
                    && $data['user_uuid'] === 'actor-uuid'
                    && $data['type'] === AuditableEventType::CREATE
                    && $data['change_from'] === null
                    && ! array_key_exists('password', $data['change_to']);
            }))
            ->andReturn(new Audit);

        $this->auditService->recordCreated($model);
    }

    /**
     * @return void
     */
    public function testRecordUpdatedPersistsFromAndTo(): void
    {
        $actor = User::factory()->make(['uuid' => 'actor-uuid']);
        Auth::setUser($actor);
        $model = User::factory()->make(['uuid' => 'subject-uuid']);

        $this->auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['type'] === AuditableEventType::UPDATE
                    && $data['change_from'] === ['first_name' => 'Old']
                    && $data['change_to'] === ['first_name' => 'New'];
            }))
            ->andReturn(new Audit);

        $this->auditService->recordUpdated($model, ['first_name' => 'Old'], ['first_name' => 'New']);
    }

    /**
     * @return void
     */
    public function testRecordDeletedPersistsFullSnapshotAsFrom(): void
    {
        $actor = User::factory()->make(['uuid' => 'actor-uuid']);
        Auth::setUser($actor);
        $model = User::factory()->make(['uuid' => 'subject-uuid']);

        $this->auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['type'] === AuditableEventType::DELETE
                    && $data['change_to'] === null
                    && ! array_key_exists('password', $data['change_from']);
            }))
            ->andReturn(new Audit);

        $this->auditService->recordDeleted($model);
    }

    /**
     * @return void
     */
    public function testRecordSyncDoesNothingWhenNoAttachedOrDetached(): void
    {
        $actor = User::factory()->make(['uuid' => 'actor-uuid']);
        Auth::setUser($actor);
        $model = User::factory()->make(['uuid' => 'subject-uuid']);

        $this->auditRepository->shouldNotReceive('create');

        $this->auditService->recordSync($model, 'job_positions', ['attached' => [], 'detached' => [], 'updated' => []]);
    }

    /**
     * @return void
     */
    public function testRecordSyncPersistsAttachedAndDetachedUnderRelationKey(): void
    {
        $actor = User::factory()->make(['uuid' => 'actor-uuid']);
        Auth::setUser($actor);
        $model = User::factory()->make(['uuid' => 'subject-uuid']);

        $this->auditRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['change_from'] === ['job_positions' => ['old-uuid']]
                    && $data['change_to'] === ['job_positions' => ['new-uuid']];
            }))
            ->andReturn(new Audit);

        $this->auditService->recordSync($model, 'job_positions', [
            'attached' => ['new-uuid'],
            'detached' => ['old-uuid'],
            'updated' => [],
        ]);
    }

    /**
     * @return void
     */
    public function testResolveAuditableDelegatesToRepositoryWithMappedModelClass(): void
    {
        $user = User::factory()->make(['uuid' => 'subject-uuid']);

        $this->auditRepository
            ->shouldReceive('findAuditableOrFail')
            ->once()
            ->with(User::class, 'subject-uuid')
            ->andReturn($user);

        $result = $this->auditService->resolveAuditable('user', 'subject-uuid');

        $this->assertSame($user, $result);
    }

    /**
     * @return void
     */
    public function testResolveAuditableThrowsForUnknownResource(): void
    {
        $this->auditRepository->shouldNotReceive('findAuditableOrFail');

        $this->expectException(InvalidAuditableIdentifierException::class);

        $this->auditService->resolveAuditable('unknown-resource', 'subject-uuid');
    }
}
