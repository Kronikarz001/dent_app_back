<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\PhoneNumber;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhoneNumberTest
 */
final class PhoneNumberTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPhoneNumberableReturnsOwningUser(): void
    {
        $user = User::factory()->create();
        $phoneNumber = PhoneNumber::factory()->create([
            'phoneable_type' => User::class,
            'phoneable_uuid' => $user->uuid,
        ]);

        $this->assertTrue($phoneNumber->phoneNumberable->is($user));
    }

    /**
     * @return void
     */
    public function testPhoneNumberableReturnsOwningPatient(): void
    {
        $patient = Patient::factory()->create();
        $phoneNumber = PhoneNumber::factory()->create([
            'phoneable_type' => Patient::class,
            'phoneable_uuid' => $patient->uuid,
        ]);

        $this->assertTrue($phoneNumber->phoneNumberable->is($patient));
    }
}
