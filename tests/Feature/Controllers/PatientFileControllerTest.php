<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\Patient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of PatientFileControllerTest
 */
class PatientFileControllerTest extends TestCase
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
        $patient = Patient::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.index', ['patient' => $patient->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.store', ['patient' => $patient->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $patient = Patient::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.show', ['patient' => $patient->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $patient->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.download', ['patient' => $patient->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $patient = Patient::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
            'path' => 'patient/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.download', ['patient' => $patient->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $patient = Patient::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::PATIENT->value,
            'fileable_id' => $patient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patientfile.update', ['patient' => $patient->uuid, 'file' => $file->uuid]), [
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
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $patient->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('patientfile.destroy', ['patient' => $patient->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $patient = Patient::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.store', ['patient' => $patient->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $patient->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patientfile.newversion', ['patient' => $patient->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }
}
