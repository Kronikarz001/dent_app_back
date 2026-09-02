<?php

namespace Tests\Feature\Services;

use App\Models\JobPosition;
use App\Models\User;
use App\Services\DentistServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of DentistServiceTest
 */
class DentistServiceTest extends TestCase
{
    /**
     * @var DentistServiceInterface|Application|mixed|object
     */
    private DentistServiceInterface $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DentistServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetDentistsReturnsOnlyUsersWithDentistJobPosition(): void
    {
        $dentistPosition = JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
        $otherPosition = JobPosition::factory()->create();

        $dentist = User::factory()->create();
        $dentist->update(['job_position_uuid' => $dentistPosition->uuid]);

        $other = User::factory()->create();
        $other->update(['job_position_uuid' => $otherPosition->uuid]);

        $result = $this->service->getDentists();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $uuids = collect($result->items())->pluck('uuid')->all();
        $this->assertContains($dentist->uuid, $uuids);
        $this->assertNotContains($other->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testGetDentistsListReturnsPaginator(): void
    {
        $dentistPosition = JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
        $dentist = User::factory()->create();
        $dentist->update(['job_position_uuid' => $dentistPosition->uuid]);

        $result = $this->service->getDentistsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
