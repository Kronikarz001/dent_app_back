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
    public function test_index_returns_success_response(): void
    {
        $user = User::factory()->create();

        $this->callApiWithLoggedUser($user)
            ->getJson(route('userfile.index', ['user' => $user->uuid]))
            ->assertOk();
    }

    public function test_store_returns_created_response(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser($user)
            ->post(route('userfile.store', ['user' => $user->uuid]), [
                'files' => [$file],
            ])
            ->assertCreated();
    }

    public function test_show_returns_success_response(): void
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
            ->getJson(route('userfile.show', ['user' => $user->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function test_update_returns_success_response(): void
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

    public function test_destroy_returns_no_content_response(): void
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

    public function test_download_returns_success_response(): void
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

    public function test_store_new_version_returns_created_response(): void
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
            ->assertCreated();
    }

    public function test_index_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->getJson(route('userfile.index', ['user' => $user->uuid]))
            ->assertUnauthorized();
    }
}
