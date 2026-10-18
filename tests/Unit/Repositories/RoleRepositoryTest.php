<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of RoleRepositoryTest
 */
class RoleRepositoryTest extends TestCase
{
    /**
     * @var RoleRepositoryInterface
     */
    private RoleRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(RoleRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsRole(): void
    {
        $role = $this->repository->create(['name' => 'Testowa rola']);

        $this->assertDatabaseHas('roles', ['uuid' => $role->uuid, 'name' => 'Testowa rola']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingRole(): void
    {
        $role = Role::factory()->create();

        $this->assertTrue($this->repository->findByUuid($role->uuid)->is($role));
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
    public function testUpdateChangesRoleAttributes(): void
    {
        $role = Role::factory()->create();

        $updated = $this->repository->update($role, ['name' => 'Zmieniona']);

        $this->assertSame('Zmieniona', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesRole(): void
    {
        $role = Role::factory()->create();

        $this->assertTrue($this->repository->delete($role));
        $this->assertDatabaseMissing('roles', ['uuid' => $role->uuid]);
    }
}
