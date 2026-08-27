<?php

namespace Tests\Feature\Services;

use App\Enums\PhoneNumberType;
use App\Exceptions\PhoneNumberAlreadyAssignedException;
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

        $this->service->assignPhone($user, $phones);

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '500100200',
            'type' => PhoneNumberType::WORK->value,
            'phoneable_type' => User::class,
            'phoneable_uuid' => $user->uuid,
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
        $this->service->assignPhone($user, $phones);

        $updated = [['number' => '500100200', 'type' => PhoneNumberType::PRIVATE->value]];
        $this->service->assignPhone($user, $updated);

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '500100200',
            'type' => PhoneNumberType::PRIVATE->value,
        ]);
        $this->assertSame(1, PhoneNumber::where('number', '500100200')->count());
    }

    /**
     * @return void
     */
    public function testAssignPhonesUpdatesNumberForSameOwnerAndType(): void
    {
        $user = User::factory()->create();
        $phones = [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]];
        $this->service->assignPhone($user, $phones);

        $updated = [['number' => '600200300', 'type' => PhoneNumberType::WORK->value]];
        $this->service->assignPhone($user, $updated);

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'phoneable_uuid' => $user->uuid,
            'type' => PhoneNumberType::WORK->value,
            'number' => '600200300',
        ]);
        $this->assertDatabaseMissing(self::PHONE_NUMBERS_TABLE, ['number' => '500100200']);
        $this->assertSame(1, PhoneNumber::where('phoneable_uuid', $user->uuid)->where('type', PhoneNumberType::WORK->value)->count());
    }

    /**
     * @return void
     */
    public function testAssignPhoneRejectsNumberAlreadyOwnedByAnotherEntity(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->service->assignPhone($owner, [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]]);

        $this->expectException(PhoneNumberAlreadyAssignedException::class);

        $this->service->assignPhone($otherUser, [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]]);
    }

    /**
     * @return void
     */
    public function testAssignPhoneRejectsNumberAlreadyOwnedByAnotherEntityWithoutReassigningIt(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->service->assignPhone($owner, [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]]);

        try {
            $this->service->assignPhone($otherUser, [['number' => '500100200', 'type' => PhoneNumberType::WORK->value]]);
        } catch (PhoneNumberAlreadyAssignedException) {
            // expected
        }

        $this->assertDatabaseHas(self::PHONE_NUMBERS_TABLE, [
            'number' => '500100200',
            'phoneable_type' => User::class,
            'phoneable_uuid' => $owner->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testAssignPhonesWithEmptyArrayDoesNothing(): void
    {
        $user = User::factory()->create();

        $this->service->assignPhone($user, []);

        $this->assertDatabaseMissing(self::PHONE_NUMBERS_TABLE, ['phoneable_uuid' => $user->uuid]);
    }
}
