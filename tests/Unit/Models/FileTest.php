<?php

namespace Tests\Unit\Models;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\Patient;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of FileTest
 */
final class FileTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUserReturnsUploader(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['user_uuid' => $user->uuid]);

        $this->assertTrue($file->user->is($user));
    }

    /**
     * @return void
     */
    public function testFilesReturnsNewerVersionsOfThisFile(): void
    {
        $patient = Patient::factory()->create();
        $original = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
            'is_latest' => false,
        ]);
        $newVersion = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
            'file_uuid' => $original->uuid,
            'is_latest' => true,
        ]);

        $this->assertTrue($original->files->contains($newVersion));
    }
}
