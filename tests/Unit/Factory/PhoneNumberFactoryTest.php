<?php

namespace Tests\Unit\Factory;

use App\Models\PhoneNumber;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhoneNumberFactoryTest
 */
final class PhoneNumberFactoryTest extends UnitTestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function test_phone_number_create_by_factory(): void
    {
        $phoneNumber = PhoneNumber::factory()->create(['number' => '+48123456789']);

        $this->assertEquals('+48123456789', $phoneNumber->number);
    }
}
