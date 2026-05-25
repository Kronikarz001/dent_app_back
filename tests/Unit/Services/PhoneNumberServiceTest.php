<?php

namespace Tests\Unit\Services;

use App\Models\Patient;
use App\Models\PhoneNumber;
use App\Services\PhoneNumberService;
use Tests\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    private PhoneNumberService $phoneNumberService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->phoneNumberService = new PhoneNumberService;
    }

    /**
     * @return void
     */
    public function test_assign_phones_inserts_records_with_correct_data(): void
    {
        $patient = Patient::factory()->create();

        $phones = [
            ['number' => '100200300', 'type' => 'PRIVATE'],
            ['number' => '900800700', 'type' => 'WORK'],
        ];

        $this->phoneNumberService->assignPhones($patient, $phones);

        $this->assertDatabaseHas('phone_numbers', [
            'number' => '100200300',
            'type' => 'PRIVATE',
            'phoneable_type' => Patient::class,
            'phoneable_id' => $patient->uuid,
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'number' => '900800700',
            'type' => 'WORK',
            'phoneable_type' => Patient::class,
            'phoneable_id' => $patient->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function test_assign_phones_updates_type_on_duplicate_number(): void
    {
        $patient = Patient::factory()->create();

        PhoneNumber::factory()->create([
            'number' => '100200300',
            'type' => 'PRIVATE',
            'phoneable_type' => Patient::class,
            'phoneable_id' => $patient->uuid,
        ]);

        $this->phoneNumberService->assignPhones($patient, [
            ['number' => '100200300', 'type' => 'WORK'],
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'number' => '100200300',
            'type' => 'WORK',
        ]);

        $this->assertDatabaseCount('phone_numbers', 1);
    }

    /**
     * @return void
     */
    public function test_assign_phones_with_empty_array_does_nothing(): void
    {
        $patient = Patient::factory()->create();

        $this->phoneNumberService->assignPhones($patient, []);

        $this->assertDatabaseCount('phone_numbers', 0);
    }
}
