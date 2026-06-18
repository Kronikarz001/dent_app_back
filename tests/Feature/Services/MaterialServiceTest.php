<?php

namespace Tests\Feature\Services;

use App\Models\DentalExamination;
use App\Models\Material;
use App\Services\MaterialServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of MaterialServiceTest
 */
class MaterialServiceTest extends TestCase
{
    /**
     * @var MaterialServiceInterface|Application|mixed|object
     */
    private MaterialServiceInterface $service;

    protected const MATERIALS_TABLE = 'materials';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MaterialServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetMaterialsReturnsPaginatedResults(): void
    {
        Material::factory()->count(3)->create();

        $result = $this->service->getMaterials();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetMaterialsListReturnsPaginator(): void
    {
        Material::factory()->count(2)->create();

        $result = $this->service->getMaterialsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateMaterialPersistsToDatabase(): void
    {
        $data = Material::factory()->make()->toArray();

        $material = $this->service->createMaterial($data);

        $this->assertInstanceOf(Material::class, $material);
        $this->assertDatabaseHas(self::MATERIALS_TABLE, ['uuid' => $material->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateMaterialPersistsChangesToDatabase(): void
    {
        $material = Material::factory()->create();

        $result = $this->service->updateMaterial($material, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(Material::class, $result);
        $this->assertDatabaseHas(self::MATERIALS_TABLE, ['uuid' => $material->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteMaterialRemovesFromDatabase(): void
    {
        $material = Material::factory()->create();

        $this->service->deleteMaterial($material);

        $this->assertDatabaseMissing(self::MATERIALS_TABLE, ['uuid' => $material->uuid]);
    }

    /**
     * @return void
     */
    public function testCreateMaterialSyncsDentalExaminations(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $data = array_merge(Material::factory()->make()->toArray(), [
            'dental_examinations' => [$dentalExamination->uuid],
        ]);

        $material = $this->service->createMaterial($data);

        $this->assertDatabaseHas('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $dentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateMaterialSyncsDentalExaminationsReplacingPrevious(): void
    {
        $material = Material::factory()->create();
        $oldDentalExamination = DentalExamination::factory()->create();
        $newDentalExamination = DentalExamination::factory()->create();
        $material->dentalExaminations()->sync([$oldDentalExamination->uuid]);

        $this->service->updateMaterial($material, ['dental_examinations' => [$newDentalExamination->uuid]]);

        $this->assertDatabaseMissing('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $oldDentalExamination->uuid,
        ]);
        $this->assertDatabaseHas('dental_examinations_materials', [
            'material_uuid' => $material->uuid,
            'dental_examination_uuid' => $newDentalExamination->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testMaterialCanBeAssignedToMultipleDentalExaminations(): void
    {
        $materialA = Material::factory()->create();
        $materialB = Material::factory()->create();
        $dentalExamination = DentalExamination::factory()->create();

        $this->service->updateMaterial($materialA, ['dental_examinations' => [$dentalExamination->uuid]]);
        $this->service->updateMaterial($materialB, ['dental_examinations' => [$dentalExamination->uuid]]);

        $this->assertCount(2, $dentalExamination->materials()->get());
    }
}
