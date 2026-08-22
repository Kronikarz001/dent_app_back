<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of MessageFileControllerTest
 */
class MessageFileControllerTest extends TestCase
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
        $message = Message::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $message->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('messagefile.index', ['message' => $message->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $message = Message::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('messagefile.store', ['message' => $message->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $message->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $message = Message::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('messagefile.store', ['message' => $message->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $message = Message::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $message->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('messagefile.show', ['message' => $message->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $message = Message::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('messagefile.store', ['message' => $message->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $message->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('messagefile.download', ['message' => $message->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $message = Message::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $message->uuid,
            'path' => 'message/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('messagefile.download', ['message' => $message->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $message = Message::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::MESSAGE->value,
            'fileable_id' => $message->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('messagefile.update', ['message' => $message->uuid, 'file' => $file->uuid]), [
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
        $message = Message::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('messagefile.store', ['message' => $message->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $message->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('messagefile.destroy', ['message' => $message->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }
}
