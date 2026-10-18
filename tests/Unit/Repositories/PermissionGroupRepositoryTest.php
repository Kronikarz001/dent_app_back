<?php

namespace Tests\Unit\Repositories;

use App\Models\PermissionGroup;
use App\Repositories\PermissionGroupRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of PermissionGroupRepositoryTest
 */
class PermissionGroupRepositoryTest extends TestCase
{
    /**
     * @var PermissionGroupRepositoryInterface
     */
    private PermissionGroupRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PermissionGroupRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsPermissionGroup(): void
    {
        $group = $this->repository->create(['name' => 'Testowa grupa']);

        $this->assertDatabaseHas('permission_groups', ['uuid' => $group->uuid, 'name' => 'Testowa grupa']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingGroup(): void
    {
        $group = PermissionGroup::factory()->create();

        $this->assertTrue($this->repository->findByUuid($group->uuid)->is($group));
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repository->findByUuid((string) Str::uuid()));
    }

    /**
     * @return void
     */
    public function testUpdateChangesGroupAttributes(): void
    {
        $group = PermissionGroup::factory()->create();

        $updated = $this->repository->update($group, ['name' => 'Zmieniona']);

        $this->assertSame('Zmieniona', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesGroup(): void
    {
        $group = PermissionGroup::factory()->create();

        $this->assertTrue($this->repository->delete($group));
        $this->assertDatabaseMissing('permission_groups', ['uuid' => $group->uuid]);
    }
}
