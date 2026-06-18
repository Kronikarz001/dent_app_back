<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\DentalExamination;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of DentalExaminationFileControllerTest
 */
class DentalExaminationFileControllerTest extends TestCase
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
        $dentalExamination = DentalExamination::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::DENTAL_EXAMINATION->value,
            'fileable_id' => $dentalExamination->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalexaminationfile.index', ['dentalExamination' => $dentalExamination->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.store', ['dentalExamination' => $dentalExamination->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::DENTAL_EXAMINATION->value,
            'fileable_id' => $dentalExamination->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $dentalExamination = DentalExamination::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.store', ['dentalExamination' => $dentalExamination->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::DENTAL_EXAMINATION->value,
            'fileable_id' => $dentalExamination->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalexaminationfile.show', ['dentalExamination' => $dentalExamination->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.store', ['dentalExamination' => $dentalExamination->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $dentalExamination->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalexaminationfile.download', ['dentalExamination' => $dentalExamination->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::DENTAL_EXAMINATION->value,
            'fileable_id' => $dentalExamination->uuid,
            'path' => 'dental_examination/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('dentalexaminationfile.download', ['dentalExamination' => $dentalExamination->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::DENTAL_EXAMINATION->value,
            'fileable_id' => $dentalExamination->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('dentalexaminationfile.update', ['dentalExamination' => $dentalExamination->uuid, 'file' => $file->uuid]), [
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
        $dentalExamination = DentalExamination::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.store', ['dentalExamination' => $dentalExamination->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $dentalExamination->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('dentalexaminationfile.destroy', ['dentalExamination' => $dentalExamination->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $dentalExamination = DentalExamination::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.store', ['dentalExamination' => $dentalExamination->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $dentalExamination->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('dentalexaminationfile.newversion', ['dentalExamination' => $dentalExamination->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }
}
