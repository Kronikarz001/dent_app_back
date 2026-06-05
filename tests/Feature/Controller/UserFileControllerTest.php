<?php

namespace Tests\Feature\Controller;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserFileControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse(): void
    {
        $user = User::factory()->create();

        $this->callApiWithLoggedUser($user)
            ->getJson(route('userfile.index', ['user' => $user->uuid]))
            ->assertOk();
    }

    public function testStoreReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->post(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$file],
            ])
            ->assertOk();
    }

    public function testShowReturnsSuccessResponse(): void
    {
        $user = User::factory()->create();
        $fileModel = File::factory()->create([
            'fileable_id' => $user->uuid,
            'fileable_type' => User::class,
            'user_uuid' => $user->uuid,
        ]);

        $this->callApiWithLoggedUser($user)
            ->getJson(route('userfile.show', ['user' => $user->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testDownloadReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $path = 'user/ab/cd/ef/abcdef/file';
        $fileModel = File::factory()->create([
            'path' => $path,
            'fileable_id' => $user->uuid,
            'fileable_type' => User::class,
            'user_uuid' => $user->uuid,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('test content'));

        $this->callApiWithLoggedUser($user)
            ->getJson(route('userfile.download', ['user' => $user->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testUpdateReturnsSuccessResponse(): void
    {
        $user = User::factory()->create();
        $fileModel = File::factory()->create([
            'fileable_id' => $user->uuid,
            'fileable_type' => User::class,
            'user_uuid' => $user->uuid,
        ]);

        $this->callApiWithLoggedUser($user)
            ->putJson(route('userfile.update', ['user' => $user->uuid, 'file' => $fileModel->uuid]), [
                'filename' => 'new_name',
            ])
            ->assertOk();
    }

    public function testDestroyReturnsNoContentResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $path = 'user/ab/cd/ef/abcdef/file';
        $fileModel = File::factory()->create([
            'path' => $path,
            'fileable_id' => $user->uuid,
            'fileable_type' => User::class,
            'user_uuid' => $user->uuid,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('test'));

        $this->callApiWithLoggedUser($user)
            ->deleteJson(route('userfile.destroy', ['user' => $user->uuid, 'file' => $fileModel->uuid]))
            ->assertNoContent();
    }

    public function testStoreNewVersionReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $path = 'user/ab/cd/ef/abcdef/old';
        $existingFile = File::factory()->create([
            'path' => $path,
            'fileable_id' => $user->uuid,
            'fileable_type' => User::class,
            'user_uuid' => $user->uuid,
            'is_latest' => true,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('old content'));

        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->post(route('userfile.newversion', ['user' => $user->uuid, 'file' => $existingFile->uuid]), [
                'files' => [$newFile],
            ])
            ->assertOk();
    }

    public function testIndexRequiresAuthentication(): void
    {
        $user = User::factory()->create();

        $this->getJson(route('userfile.index', ['user' => $user->uuid]))
            ->assertUnauthorized();
    }
}
