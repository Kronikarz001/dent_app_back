<?php

namespace Tests\Unit\Repositories;

use App\Models\UserGroup;
use App\Repositories\UserGroupRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of UserGroupRepositoryTest
 */
class UserGroupRepositoryTest extends TestCase
{
    /**
     * @var UserGroupRepositoryInterface
     */
    private UserGroupRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(UserGroupRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsUserGroup(): void
    {
        $group = $this->repository->create(['name' => 'Testowa grupa']);

        $this->assertDatabaseHas('user_groups', ['uuid' => $group->uuid, 'name' => 'Testowa grupa']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingGroup(): void
    {
        $group = UserGroup::factory()->create();

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
        $group = UserGroup::factory()->create();

        $updated = $this->repository->update($group, ['name' => 'Zmieniona']);

        $this->assertSame('Zmieniona', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesGroup(): void
    {
        $group = UserGroup::factory()->create();

        $this->assertTrue($this->repository->delete($group));
        $this->assertDatabaseMissing('user_groups', ['uuid' => $group->uuid]);
    }
}
