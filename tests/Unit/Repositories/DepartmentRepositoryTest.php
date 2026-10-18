<?php

namespace Tests\Unit\Repositories;

use App\Models\Department;
use App\Repositories\DepartmentRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of DepartmentRepositoryTest
 */
class DepartmentRepositoryTest extends TestCase
{
    /**
     * @var DepartmentRepositoryInterface
     */
    private DepartmentRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(DepartmentRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsDepartment(): void
    {
        $department = $this->repository->create(['name' => 'Testowy dział']);

        $this->assertDatabaseHas('departments', ['uuid' => $department->uuid, 'name' => 'Testowy dział']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingDepartment(): void
    {
        $department = Department::factory()->create();

        $this->assertTrue($this->repository->findByUuid($department->uuid)->is($department));
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
    public function testUpdateChangesDepartmentAttributes(): void
    {
        $department = Department::factory()->create();

        $updated = $this->repository->update($department, ['name' => 'Zmieniony']);

        $this->assertSame('Zmieniony', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesDepartment(): void
    {
        $department = Department::factory()->create();

        $this->assertTrue($this->repository->delete($department));
        $this->assertDatabaseMissing('departments', ['uuid' => $department->uuid]);
    }
}
