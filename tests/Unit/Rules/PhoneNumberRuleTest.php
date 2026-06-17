<?php

namespace Tests\Unit\Rules;

use App\Rules\PhoneNumberRule;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhoneNumberRuleTest
 */
class PhoneNumberRuleTest extends UnitTestCase
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
    public function testPassesForValidPolishNumber(): void
    {
        $this->assertRulePasses('48123456789');
    }

    /**
     * @return void
     */
    public function testPassesForValidPolishNumberWithSeparators(): void
    {
        $this->assertRulePasses('48 123-456-789');
    }

    /**
     * @return void
     */
    public function testPassesForValidPolishNumberWithParentheses(): void
    {
        $this->assertRulePasses('(48)123456789');
    }

    /**
     * @return void
     */
    public function testFailsForPolishNumberTooShort(): void
    {
        $this->assertRuleFails('4812345678');
    }

    /**
     * @return void
     */
    public function testFailsForPolishNumberTooLong(): void
    {
        $this->assertRuleFails('481234567890');
    }

    /**
     * @return void
     */
    public function testPassesForValidGermanNumberAtMinLength(): void
    {
        $this->assertRulePasses('491234567');
    }

    /**
     * @return void
     */
    public function testPassesForValidGermanNumberAtMaxLength(): void
    {
        $this->assertRulePasses('4912345678901');
    }

    /**
     * @return void
     */
    public function testFailsForGermanNumberTooShort(): void
    {
        $this->assertRuleFails('4912345');
    }

    /**
     * @return void
     */
    public function testFailsForGermanNumberTooLong(): void
    {
        $this->assertRuleFails('491234567890123');
    }

    /**
     * @return void
     */
    public function testFailsForUnsupportedCountryPrefix(): void
    {
        $this->assertRuleFails('50123456789');
    }

    /**
     * @return void
     */
    public function testFailsForLeadingPlusSign(): void
    {
        $this->assertRuleFails('+48123456789');
    }

    /**
     * @return void
     */
    public function testFailsForNonNumericValue(): void
    {
        $this->assertRuleFails('not-a-number');
    }

    /**
     * @param mixed $value
     * @return void
     */
    private function assertRulePasses(mixed $value): void
    {
        $failed = false;

        (new PhoneNumberRule)->validate('phone_numbers.number', $value, function () use (&$failed) {
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

        (new PhoneNumberRule)->validate('phone_numbers.number', $value, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
