<?php

namespace Tests\Unit\Enums;

use App\Enums\FileableType;
use App\Exceptions\FileableTypeException;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserAvatar;
use App\Models\UserBackground;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of FileableTypeTest
 */
final class FileableTypeTest extends UnitTestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function casesProvider(): array
    {
        return [
            'user' => ['USER', User::class, 'user'],
            'patient' => ['PATIENT', Patient::class, 'patient'],
            'job position' => ['JOB_POSITION', JobPosition::class, 'job_position'],
            'user avatar' => ['USER_AVATAR', UserAvatar::class, 'user_avatar'],
            'user background' => ['USER_BACKGROUND', UserBackground::class, 'user_background'],
            'dental examination' => ['DENTAL_EXAMINATION', DentalExamination::class, 'dental_examination'],
            'material' => ['MATERIAL', Material::class, 'material'],
            'message' => ['MESSAGE', Message::class, 'message'],
            'calendar' => ['CALENDAR', Calendar::class, 'calendar'],
        ];
    }

    /**
     * @param string $name
     * @param string $modelClass
     * @param string $directory
     * @return void
     */
    #[DataProvider('casesProvider')]
    public function testMapResolvesCaseByName(string $name, string $modelClass, string $directory): void
    {
        $type = FileableType::map($name);

        $this->assertSame($name, $type->name);
        $this->assertSame($modelClass, $type->value);
        $this->assertSame($directory, $type->getDefaultDirectory());
    }

    /**
     * @return void
     */
    public function testProviderCoversEveryCase(): void
    {
        $this->assertCount(count(FileableType::cases()), self::casesProvider());
    }

    /**
     * @return void
     */
    public function testDefaultDirectoriesAreUnique(): void
    {
        $directories = array_map(fn (FileableType $type) => $type->getDefaultDirectory(), FileableType::cases());

        $this->assertSame($directories, array_values(array_unique($directories)));
    }

    /**
     * @return void
     */
    public function testMapThrowsForUnknownName(): void
    {
        $this->expectException(FileableTypeException::class);

        FileableType::map('PRESCRIPTION');
    }

    /**
     * @return void
     */
    public function testMapIsCaseSensitive(): void
    {
        $this->expectException(FileableTypeException::class);

        FileableType::map('user');
    }
}
