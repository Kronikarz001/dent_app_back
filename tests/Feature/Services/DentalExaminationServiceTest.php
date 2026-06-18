<?php

namespace Tests\Feature\Services;

use App\Enums\AuditableEventType;
use App\Models\Audit;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\Material;
use App\Models\User;
use App\Services\DentalExaminationServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

    /**
     * @return void
     */
    public function testCreateDentalExaminationSyncsMaterials(): void
    {
        $material = Material::factory()->create();
        $data = array_merge(DentalExamination::factory()->make()->toArray(), [
            'materials' => [$material->uuid],
        ]);

        $dentalExamination = $this->service->createDentalExamination($data);

        $this->assertDatabaseHas('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $material->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationSyncsMaterialsReplacingPrevious(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $oldMaterial = Material::factory()->create();
        $newMaterial = Material::factory()->create();
        $dentalExamination->materials()->sync([$oldMaterial->uuid]);

        $this->service->updateDentalExamination($dentalExamination, ['materials' => [$newMaterial->uuid]]);

        $this->assertDatabaseMissing('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $oldMaterial->uuid,
        ]);
        $this->assertDatabaseHas('dental_examinations_materials', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'material_uuid' => $newMaterial->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testDentalExaminationCanBeAssignedToMultipleMaterials(): void
    {
        $dentalExaminationA = DentalExamination::factory()->create();
        $dentalExaminationB = DentalExamination::factory()->create();
        $material = Material::factory()->create();

        $this->service->updateDentalExamination($dentalExaminationA, ['materials' => [$material->uuid]]);
        $this->service->updateDentalExamination($dentalExaminationB, ['materials' => [$material->uuid]]);

        $this->assertCount(2, $material->dentalExaminations()->get());
    }

    /**
     * @return void
     */
    public function testCreateDentalExaminationSyncsCalendars(): void
    {
        $calendar = Calendar::factory()->create();
        $data = array_merge(DentalExamination::factory()->make()->toArray(), [
            'calendars' => [$calendar->uuid],
        ]);

        $dentalExamination = $this->service->createDentalExamination($data);

        $this->assertDatabaseHas('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $calendar->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationSyncsCalendarsReplacingPrevious(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $oldCalendar = Calendar::factory()->create();
        $newCalendar = Calendar::factory()->create();
        $dentalExamination->calendars()->sync([$oldCalendar->uuid]);

        $this->service->updateDentalExamination($dentalExamination, ['calendars' => [$newCalendar->uuid]]);

        $this->assertDatabaseMissing('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $oldCalendar->uuid,
        ]);
        $this->assertDatabaseHas('calendars_dental_examinations', [
            'dental_examination_uuid' => $dentalExamination->uuid,
            'calendar_uuid' => $newCalendar->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateDentalExaminationSyncingMaterialsRecordsAuditEntryWhenAuthenticated(): void
    {
        $actor = User::factory()->create();
        Auth::setUser($actor);

        $dentalExamination = DentalExamination::factory()->create();
        $material = Material::factory()->create();

        $this->service->updateDentalExamination($dentalExamination, ['materials' => [$material->uuid]]);

        $audit = Audit::query()
            ->where('auditable_id', $dentalExamination->uuid)
            ->where('type', AuditableEventType::UPDATE->value)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($actor->uuid, $audit->user_uuid);
        $this->assertSame(['materials' => [$material->uuid]], $audit->change_to);
    }
}
