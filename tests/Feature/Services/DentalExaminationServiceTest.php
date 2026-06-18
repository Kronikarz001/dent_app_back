<?php

namespace Tests\Feature\Services;

use App\Models\DentalExamination;
use App\Services\DentalExaminationServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of DentalExaminationServiceTest
 */
class DentalExaminationServiceTest extends TestCase
{
    /**
     * @var DentalExaminationServiceInterface|Application|mixed|object
     */
    private DentalExaminationServiceInterface $service;

    protected const DENTAL_EXAMINATIONS_TABLE = 'dental_examinations';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DentalExaminationServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetDentalExaminationsReturnsPaginatedResults(): void
    {
        DentalExamination::factory()->count(3)->create();

        $result = $this->service->getDentalExaminations();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetDentalExaminationsListReturnsPaginator(): void
    {
        DentalExamination::factory()->count(2)->create();

        $result = $this->service->getDentalExaminationsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateDentalExaminationPersistsToDatabase(): void
    {
        $data = DentalExamination::factory()->make()->toArray();

        $dentalExamination = $this->service->createDentalExamination($data);

        $this->assertInstanceOf(DentalExamination::class, $dentalExamination);
        $this->assertDatabaseHas(self::DENTAL_EXAMINATIONS_TABLE, ['uuid' => $dentalExamination->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationPersistsChangesToDatabase(): void
    {
        $dentalExamination = DentalExamination::factory()->create();

        $result = $this->service->updateDentalExamination($dentalExamination, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(DentalExamination::class, $result);
        $this->assertDatabaseHas(self::DENTAL_EXAMINATIONS_TABLE, ['uuid' => $dentalExamination->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteDentalExaminationRemovesFromDatabase(): void
    {
        $dentalExamination = DentalExamination::factory()->create();

        $this->service->deleteDentalExamination($dentalExamination);

        $this->assertDatabaseMissing(self::DENTAL_EXAMINATIONS_TABLE, ['uuid' => $dentalExamination->uuid]);
    }
}
