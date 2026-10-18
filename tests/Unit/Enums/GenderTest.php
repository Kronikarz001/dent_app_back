<?php

namespace Tests\Unit\Enums;

use App\Enums\Gender;
use Tests\Unit\UnitTestCase;

/**
 * Summary of GenderTest
 */
final class GenderTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFromPeselReadsTenthDigit(): void
    {
        $this->assertSame(Gender::FEMALE, Gender::fromPesel('90010112345'));
        $this->assertSame(Gender::MALE, Gender::fromPesel('90010112335'));
    }

    /**
     * @return void
     */
    public function testFromPeselReturnsNullForMissingOrMalformedNumber(): void
    {
        $this->assertNull(Gender::fromPesel(null));
        $this->assertNull(Gender::fromPesel(''));
        $this->assertNull(Gender::fromPesel('123'));
    }
}
