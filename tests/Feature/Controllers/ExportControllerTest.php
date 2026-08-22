<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Patient;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Summary of ExportControllerTest
 */
class ExportControllerTest extends TestCase
{
    /**
     * @return array
     */
    public static function exportableModelsProvider(): array
    {
        return [
            'user' => [User::class, 'user.export'],
            'patient' => [Patient::class, 'patient.export'],
            'material' => [Material::class, 'material.export'],
            'jobPosition' => [JobPosition::class, 'jobPosition.export'],
            'dentalExamination' => [DentalExamination::class, 'dentalExamination.export'],
            'calendar' => [Calendar::class, 'calendar.export'],
        ];
    }

    /**
     * @param class-string $modelClass
     * @param string $routeName
     * @return void
     */
    #[DataProvider('exportableModelsProvider')]
    public function testExportReturnsSuccessResponse(string $modelClass, string $routeName): void
    {
        $modelClass::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route($routeName, ['type' => 'xlsx']));

        $response->assertOk();
    }
}
