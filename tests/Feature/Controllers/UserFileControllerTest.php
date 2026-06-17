<?php

namespace Tests\Feature\Controllers;

use App\Enums\FileableType;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of UserFileControllerTest
 */
class UserFileControllerTest extends TestCase
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
        $user = User::factory()->create();
        File::factory()->create([
            'fileable_type' => FileableType::USER->value,
            'fileable_id' => $user->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('userfile.index', ['user' => $user->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $user = User::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::USER->value,
            'fileable_id' => $user->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $user = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('userfile.store', ['user' => $user->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::USER->value,
            'fileable_id' => $user->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('userfile.show', ['user' => $user->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $user = User::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $user->uuid)->first();

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('userfile.download', ['user' => $user->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::USER->value,
            'fileable_id' => $user->uuid,
            'path' => 'user/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('userfile.download', ['user' => $user->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => FileableType::USER->value,
            'fileable_id' => $user->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('userfile.update', ['user' => $user->uuid, 'file' => $file->uuid]), [
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
        $user = User::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $user->uuid)->first();

        $this->callApiWithLoggedUser($user)
            ->deleteJson(route('userfile.destroy', ['user' => $user->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $user = User::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $user->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.newversion', ['user' => $user->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreAvatarUploadsImage(): void
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('userfile.avatar-store', ['user' => $user->uuid]), [
                'file' => $image,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::USER_AVATAR->value,
            'fileable_id' => $user->uuid,
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testAvatarDownloadReturnsBinaryImageContent(): void
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg', 5, 5);

        $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.avatar-store', ['user' => $user->uuid]), [
                'file' => $image,
            ]);

        $file = File::query()->where('fileable_type', FileableType::USER_AVATAR->value)->first();

        $response = $this->callApiWithLoggedUser($user)
            ->get(route('userfile.avatar-download', ['user' => $user->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertHeader('Content-Type', $file->mimetype);
        $this->assertSame(file_get_contents($image->getPathname()), $response->getContent());
    }

    /**
     * @return void
     */
    public function testStoreBackgroundUploadsImage(): void
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('background.jpg');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('userfile.background-store', ['user' => $user->uuid]), [
                'file' => $image,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'fileable_type' => FileableType::USER_BACKGROUND->value,
            'fileable_id' => $user->uuid,
            'is_latest' => true,
        ]);
    }

    /**
     * @return void
     */
    public function testBackgroundDownloadReturnsBinaryImageContent(): void
    {
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('background.jpg', 5, 5);

        $this->callApiWithLoggedUser($user)
            ->postJson(route('userfile.background-store', ['user' => $user->uuid]), [
                'file' => $image,
            ]);

        $file = File::query()->where('fileable_type', FileableType::USER_BACKGROUND->value)->first();

        $response = $this->callApiWithLoggedUser($user)
            ->get(route('userfile.background-download', ['user' => $user->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertHeader('Content-Type', $file->mimetype);
        $this->assertSame(file_get_contents($image->getPathname()), $response->getContent());
    }
}
