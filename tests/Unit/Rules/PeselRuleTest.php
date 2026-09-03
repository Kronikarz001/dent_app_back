<?php

namespace Tests\Unit\Rules;

use App\Rules\PeselRule;
use Tests\TestCase;

/**
 * Summary of PeselRuleTest
 */
class PeselRuleTest extends TestCase
{
    /**
     * @return void
     */
    public function testValidPeselPassesWithoutFailure(): void
    {
        $failed = false;

        (new PeselRule)->validate('pesel', '44051401359', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    /**
     * @return void
     */
    public function testWrongCheckDigitFails(): void
    {
        $failed = false;

        (new PeselRule)->validate('pesel', '44051401350', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    /**
     * @return void
     */
    public function testNonNumericValueFails(): void
    {
        $failed = false;

        (new PeselRule)->validate('pesel', 'abcdefghijk', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    /**
     * @return void
     */
    public function testWrongLengthFails(): void
    {
        $failed = false;

        (new PeselRule)->validate('pesel', '123', function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
