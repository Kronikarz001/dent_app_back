<?php

namespace Tests\Unit\Models;

use App\Models\Dentist;
use App\Models\JobPosition;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DentistTest
 */
final class DentistTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGlobalScopeOnlyIncludesUsersWithDentistJobPosition(): void
    {
        $dentistPosition = JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
        $otherPosition = JobPosition::factory()->create(['name' => 'Recepcjonista']);

        $dentistUser = User::factory()->create(['job_position_uuid' => $dentistPosition->uuid]);
        $otherUser = User::factory()->create(['job_position_uuid' => $otherPosition->uuid]);

        $this->assertNotNull(Dentist::find($dentistUser->uuid));
        $this->assertNull(Dentist::find($otherUser->uuid));
    }

    /**
     * @return void
     */
    public function testGlobalScopeExcludesUsersWithoutJobPosition(): void
    {
        $user = User::factory()->create(['job_position_uuid' => null]);

        $this->assertNull(Dentist::find($user->uuid));
    }
}
