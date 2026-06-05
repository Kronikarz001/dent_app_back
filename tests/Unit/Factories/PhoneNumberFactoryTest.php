<?php

namespace Tests\Unit\Factories;

use App\Models\PhoneNumber;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhoneNumberFactoryTest
 */
final class PhoneNumberFactoryTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function testPhoneNumberCreateByFactory(): void
    {
        $phoneNumber = PhoneNumber::factory()->create(['number' => '+48123456789']);

        $this->assertEquals('+48123456789', $phoneNumber->number);
    }
}
