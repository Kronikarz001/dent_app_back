<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of MaterialFileControllerTest
 */
class MaterialFileControllerTest extends TestCase
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
        $material = Material::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::MATERIAL->value,
            'fileable_id' => $material->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('materialfile.index', ['material' => $material->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $material = Material::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.store', ['material' => $material->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::MATERIAL->value,
            'fileable_id' => $material->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $material = Material::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.store', ['material' => $material->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $material = Material::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MATERIAL->value,
            'fileable_id' => $material->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('materialfile.show', ['material' => $material->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $material = Material::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.store', ['material' => $material->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $material->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('materialfile.download', ['material' => $material->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $material = Material::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MATERIAL->value,
            'fileable_id' => $material->uuid,
            'path' => 'material/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('materialfile.download', ['material' => $material->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $material = Material::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MATERIAL->value,
            'fileable_id' => $material->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('materialfile.update', ['material' => $material->uuid, 'file' => $file->uuid]), [
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
        $material = Material::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.store', ['material' => $material->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $material->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('materialfile.destroy', ['material' => $material->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $material = Material::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.store', ['material' => $material->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $material->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('materialfile.newversion', ['material' => $material->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }
}
