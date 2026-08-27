<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\JobPosition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of JobPositionFileControllerTest
 */
class JobPositionFileControllerTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('files');
    }

    /**
     * @return void
     */
    public function testIndexReturnSuccessResponse(): void
    {
        $jobPosition = JobPosition::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $jobPosition->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.index', ['jobPosition' => $jobPosition->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $jobPosition->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $jobPosition->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.show', ['jobPosition' => $jobPosition->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testShowRejectsFileBelongingToAnotherJobPosition(): void
    {
        $ownJobPosition = JobPosition::factory()->create();
        $otherJobPosition = JobPosition::factory()->create();
        $foreignFile = File::factory()->create([
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $otherJobPosition->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.show', ['jobPosition' => $ownJobPosition->uuid, 'file' => $foreignFile->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $jobPosition->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.download', ['jobPosition' => $jobPosition->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $jobPosition->uuid,
            'path' => 'job_position/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.download', ['jobPosition' => $jobPosition->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::JOB_POSITION->value,
            'fileable_id' => $jobPosition->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('jobpositionfile.update', ['jobPosition' => $jobPosition->uuid, 'file' => $file->uuid]), [
                'filename' => 'renamed',
            ]);

        $response->assertOk();
        $response->assertJsonPath('filename', 'renamed');

        $this->assertDatabaseHas('files', [
            'uuid' => $file->uuid,
            'filename' => 'renamed',
        ]);
    }

    /**
     * @return void
     */
    public function testDestroyRemovesFile(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $jobPosition->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('jobpositionfile.destroy', ['jobPosition' => $jobPosition->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $jobPosition = JobPosition::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $jobPosition->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('jobpositionfile.newversion', ['jobPosition' => $jobPosition->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }
}
