<?php

namespace Tests\Unit\Repositories;

use App\Enums\FileableType;
use App\Models\File;
use App\Repositories\FileRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of FileRepositoryTest
 */
class FileRepositoryTest extends TestCase
{
    private FileRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(FileRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testFindAllByFileableExceptUuidDoesNotLeakFilesFromAnotherFileable(): void
    {
        $ownFileableId = (string) Str::uuid();
        $otherFileableId = (string) Str::uuid();
        $sharedOldFileUuid = (string) Str::uuid();

        $ownPreviousVersion = File::factory()->create([
            'uuid' => $sharedOldFileUuid,
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $ownFileableId,
        ]);

        // Belongs to a completely different fileable, but happens to share
        // the same file_uuid value that the current lookup is searching for.
        $foreignFile = File::factory()->create([
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $otherFileableId,
            'file_uuid' => $sharedOldFileUuid,
        ]);

        $result = $this->repository->findAllByFileableExceptUuid(
            FileableType::PATIENT->value,
            $ownFileableId,
            (string) Str::uuid(),
            $sharedOldFileUuid,
        );

        $this->assertTrue($result->contains('uuid', $ownPreviousVersion->uuid));
        $this->assertFalse($result->contains('uuid', $foreignFile->uuid));
    }

    /**
     * @return void
     */
    public function testFindAllByFileableExceptUuidExcludesGivenUuid(): void
    {
        $fileableId = (string) Str::uuid();

        $file = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $fileableId,
        ]);

        $result = $this->repository->findAllByFileableExceptUuid(
            FileableType::PATIENT->value,
            $fileableId,
            $file->uuid,
            null,
        );

        $this->assertFalse($result->contains('uuid', $file->uuid));
    }
}
