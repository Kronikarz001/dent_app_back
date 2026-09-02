<?php

namespace Tests\Unit\Rules;

use App\Rules\ZipCodeRule;
use Tests\Unit\UnitTestCase;

/**
 * Summary of ZipCodeRuleTest
 */
class ZipCodeRuleTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPassesForNullValue(): void
    {
        $this->assertRulePasses(null);
    }

    /**
     * @return void
     */
    public function testPassesForEmptyString(): void
    {
        $this->assertRulePasses('');
    }

    /**
     * @return void
     */
    public function testPassesForValidPolishPostalCode(): void
    {
        $this->assertRulePasses('00-001');
    }

    /**
     * @return void
     */
    public function testFailsForUsStyleZipCode(): void
    {
        $this->assertRuleFails('12345');
    }

    /**
     * @return void
     */
    public function testFailsForUsStyleZipCodeWithSuffix(): void
    {
        $this->assertRuleFails('12345-6789');
    }

    /**
     * @return void
     */
    public function testFailsForMissingDash(): void
    {
        $this->assertRuleFails('00001');
    }

    /**
     * @return void
     */
    public function testFailsForWrongDigitGrouping(): void
    {
        $this->assertRuleFails('0-00001');
    }

    /**
     * @return void
     */
    public function testFailsForNonNumericValue(): void
    {
        $this->assertRuleFails('AA-BBB');
    }

    /**
     * @param mixed $value
     * @return void
     */
    private function assertRulePasses(mixed $value): void
    {
        $failed = false;

        (new ZipCodeRule)->validate('postal_code', $value, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    /**
     * @param mixed $value
     * @return void
     */
    private function assertRuleFails(mixed $value): void
    {
        $failed = false;

        (new ZipCodeRule)->validate('postal_code', $value, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
