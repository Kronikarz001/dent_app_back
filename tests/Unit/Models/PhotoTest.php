<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\Photo;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhotoTest
 */
final class PhotoTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPatientReturnsOwningPatient(): void
    {
        $patient = Patient::factory()->create();
        $photo = Photo::factory()->create(['patient_uuid' => $patient->uuid]);

        $this->assertTrue($photo->patient->is($patient));
    }

    /**
     * @return void
     */
    public function testUserReturnsUploader(): void
    {
        $user = User::factory()->create();
        $photo = Photo::factory()->create(['user_uuid' => $user->uuid]);

        $this->assertTrue($photo->user->is($user));
    }
}
