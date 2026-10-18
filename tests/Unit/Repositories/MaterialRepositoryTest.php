<?php

namespace Tests\Unit\Repositories;

use App\Models\Material;
use App\Repositories\MaterialRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of MaterialRepositoryTest
 */
class MaterialRepositoryTest extends TestCase
{
    /**
     * @var MaterialRepositoryInterface
     */
    private MaterialRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MaterialRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsMaterial(): void
    {
        $material = $this->repository->create(['name' => 'Testowy materiał']);

        $this->assertDatabaseHas('materials', ['uuid' => $material->uuid, 'name' => 'Testowy materiał']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingMaterial(): void
    {
        $material = Material::factory()->create();

        $this->assertTrue($this->repository->findByUuid($material->uuid)->is($material));
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
    public function testUpdateChangesMaterialAttributes(): void
    {
        $material = Material::factory()->create();

        $updated = $this->repository->update($material, ['name' => 'Zmieniony']);

        $this->assertSame('Zmieniony', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesMaterial(): void
    {
        $material = Material::factory()->create();

        $this->assertTrue($this->repository->delete($material));
        $this->assertDatabaseMissing('materials', ['uuid' => $material->uuid]);
    }
}
