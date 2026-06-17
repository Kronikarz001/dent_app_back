<?php

namespace Tests\Feature\Services;

use App\Enums\PhoneNumberType;
use App\Models\PhoneNumber;
use App\Models\User;
use App\Services\PhoneNumberServiceInterface;
use Illuminate\Foundation\Application;
use Tests\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    /**
     * @var PhoneNumberServiceInterface|Application|mixed|object
     */
    private PhoneNumberServiceInterface $service;

    protected const PHONE_NUMBERS_TABLE = 'phone_numbers';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PhoneNumberServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testAssignPhonesPersistsRecordsToDatabase(): void
    {
        $user = User::factory()->create();
        $phones = [
            ['number' => '500100200', 'type' => PhoneNumberType::WORK->value],
            ['number' => '600200300', 'type' => PhoneNumberType::PRIVATE->value],
        ];

        $this->service->assignPhones($user, $phones);

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '500100200',
            'type' => PhoneNumberType::WORK->value,
            'phoneable_type' => User::class,
            'phoneable_id' => $user->uuid,
        ]);
        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '600200300',
            'type' => PhoneNumberType::PRIVATE->value,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPhonesUpdatesTypeOnDuplicateNumber(): void
    {
        $user = User::factory()->create();
        $phones = [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]];
        $this->service->assignPhones($user, $phones);

        $updated = [['number' => '500100200', 'type' => PhoneNumberType::PRIVATE->value]];
        $this->service->assignPhones($user, $updated);

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '500100200',
            'type' => PhoneNumberType::PRIVATE->value,
        ]);
        $this->assertSame(1, PhoneNumber::where('number', '500100200')->count());
    }

    /**
     * @return void
     */
    public function testAssignPhonesWithEmptyArrayDoesNothing(): void
    {
        $user = User::factory()->create();

        $this->service->assignPhones($user, []);

        $this->assertDatabaseMissing(self::PHONE_NUMBERS_TABLE, ['phoneable_id' => $user->uuid]);
    }
}
