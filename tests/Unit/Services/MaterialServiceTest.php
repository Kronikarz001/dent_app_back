<?php

namespace Tests\Unit\Services;

use App\Http\Requests\ExportRequest;
use App\Models\Material;
use App\Repositories\MaterialRepositoryInterface;
use App\Services\ExportServiceInterface;
use App\Services\MaterialService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Summary of MaterialServiceTest
 */
class MaterialServiceTest extends TestCase
{
    private MockInterface $materialRepository;

    private MockInterface $exportService;

    private MaterialService $materialService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->materialRepository = Mockery::mock(MaterialRepositoryInterface::class);
        $this->exportService = Mockery::mock(ExportServiceInterface::class);
        $this->materialService = new MaterialService($this->materialRepository, $this->exportService);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGetMaterialsDelegatesToRepositoryWithoutColumnFilter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->materialRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->materialService->getMaterials();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testGetMaterialsListPassesOnlyUuidAndNameColumns(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->materialRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->with(['uuid', 'name'])
            ->andReturn($paginator);

        $result = $this->materialService->getMaterialsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testCreateMaterialPassesDataUnchangedToRepository(): void
    {
        $data = ['name' => 'Kompozyt', 'price' => 50];
        $newMaterial = Material::factory()->make();

        $this->materialRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($newMaterial);

        $result = $this->materialService->createMaterial($data);

        $this->assertInstanceOf(Material::class, $result);
        $this->assertSame($newMaterial, $result);
    }

    /**
     * @return void
     */
    public function testUpdateMaterialPassesModelAndDataToRepository(): void
    {
        $material = Material::factory()->make();
        $data = ['name' => 'Updated'];
        $updated = Material::factory()->make(['name' => 'Updated']);

        $this->materialRepository
            ->shouldReceive('update')
            ->once()
            ->with($material, $data)
            ->andReturn($updated);

        $result = $this->materialService->updateMaterial($material, $data);

        $this->assertInstanceOf(Material::class, $result);
        $this->assertSame($updated, $result);
    }

    /**
     * @return void
     */
    public function testDeleteMaterialCallsRepositoryDeleteNotUpdate(): void
    {
        $material = Material::factory()->make();

        $this->materialRepository
            ->shouldReceive('delete')
            ->once()
            ->with($material);

        $this->materialRepository->shouldNotReceive('update');

        $this->assertNull($this->materialService->deleteMaterial($material));
    }

    /**
     * @return void
     */
    public function testExportDelegatesToExportService(): void
    {
        $paginator = new LengthAwarePaginator(collect(), 0, 15, 1);
        $request = Mockery::mock(ExportRequest::class);
        $response = Mockery::mock(BinaryFileResponse::class);

        $this->materialRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $this->exportService
            ->shouldReceive('export')
            ->once()
            ->andReturn($response);

        $result = $this->materialService->export($request);

        $this->assertSame($response, $result);
    }
}
