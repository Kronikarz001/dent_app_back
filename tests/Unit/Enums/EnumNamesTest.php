<?php

namespace Tests\Unit\Enums;

use App\Enums\PhoneNumberType;
use App\Enums\PrescriptionStatus;
use App\Enums\ReimbursementLevel;
use App\Enums\UserStatusType;
use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of EnumNamesTest
 */
final class EnumNamesTest extends UnitTestCase
{
    /**
     * @return array<string, array{BackedEnum, string}>
     */
    public static function namesProvider(): array
    {
        return [
            'phone private' => [PhoneNumberType::PRIVATE, 'Private'],
            'phone work' => [PhoneNumberType::WORK, 'Work'],
            'prescription draft' => [PrescriptionStatus::DRAFT, 'Wersja robocza'],
            'prescription issued' => [PrescriptionStatus::ISSUED, 'Wystawiona'],
            'prescription realized' => [PrescriptionStatus::REALIZED, 'Zrealizowana'],
            'prescription cancelled' => [PrescriptionStatus::CANCELLED, 'Anulowana'],
            'prescription expired' => [PrescriptionStatus::EXPIRED, 'Wygasła'],
            'reimbursement full' => [ReimbursementLevel::FULL, 'Pełna odpłatność (100%)'],
            'reimbursement flat rate' => [ReimbursementLevel::FLAT_RATE, 'Ryczałt'],
            'reimbursement free' => [ReimbursementLevel::FREE, 'Bezpłatny'],
            'reimbursement r' => [ReimbursementLevel::R, 'Kategoria R'],
            'reimbursement other' => [ReimbursementLevel::OTHER, 'Inne'],
            'user active' => [UserStatusType::ACTIVE, 'Active'],
            'user non active' => [UserStatusType::NON_ACTIVE, 'Non-Active'],
        ];
    }

    /**
     * @param BackedEnum $case
     * @param string $name
     * @return void
     */
    #[DataProvider('namesProvider')]
    public function testCaseHasExpectedNameAndValueMatchesCaseName(BackedEnum $case, string $name): void
    {
        $this->assertSame($name, $case->name());
        $this->assertSame($case->name, $case->value);
    }

    /**
     * @return void
     */
    public function testProviderCoversEveryCase(): void
    {
        $expected = count(PhoneNumberType::cases())
            + count(PrescriptionStatus::cases())
            + count(ReimbursementLevel::cases())
            + count(UserStatusType::cases());

        $this->assertCount($expected, self::namesProvider());
    }
}
