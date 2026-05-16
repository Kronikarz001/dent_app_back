<?php

namespace Tests\Feature\Controller;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarFileControllerTest extends TestCase
{
    public function testStoreReturnsCreatedResponse(): void
    {
        Storage::fake('local');

        $user   = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $this->callApiWithLoggedUser($user)
            ->post(route('useravatar.store', ['user' => $user->uuid]), [
                'avatar' => $avatar,
            ])
            ->assertCreated();
    }

    public function testStoreWhenAvatarAlreadyExistsReturnsCreated(): void
    {
        Storage::fake('local');

        $user    = User::factory()->create();
        $oldPath = 'avatar/' . $user->uuid;
        Storage::disk('local')->put($oldPath, Crypt::encrypt('old'));
        $user->update(['avatar_path' => $oldPath]);

        $avatar = UploadedFile::fake()->image('new.jpg');

        $this->callApiWithLoggedUser($user)
            ->post(route('useravatar.store', ['user' => $user->uuid]), [
                'avatar' => $avatar,
            ])
            ->assertCreated();

        Storage::disk('local')->assertExists($oldPath);
    }

    public function testShowReturnsImageResponse(): void
    {
        Storage::fake('local');

        $user   = User::factory()->create();
        $path   = 'avatar/' . $user->uuid;
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        Storage::disk('local')->put($path, Crypt::encrypt(file_get_contents($avatar->getRealPath())));
        $user->update(['avatar_path' => $path]);

        $this->callApiWithLoggedUser($user)
            ->get(route('useravatar.show', ['user' => $user->uuid]))
            ->assertOk();
    }

    public function testShowReturns404WhenNoAvatar(): void
    {
        $user = User::factory()->create();

        $this->callApiWithLoggedUser($user)
            ->getJson(route('useravatar.show', ['user' => $user->uuid]))
            ->assertNotFound();
    }

    public function testDestroyReturnsNoContentResponse(): void
    {
        Storage::fake('local');

        $user   = User::factory()->create();
        $path   = 'avatar/' . $user->uuid;
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        Storage::disk('local')->put($path, Crypt::encrypt(file_get_contents($avatar->getRealPath())));
        $user->update(['avatar_path' => $path]);

        $this->callApiWithLoggedUser($user)
            ->deleteJson(route('useravatar.destroy', ['user' => $user->uuid]))
            ->assertNoContent();

        Storage::disk('local')->assertMissing($path);
    }

    public function testDestroyWithNoAvatarReturnsNoContent(): void
    {
        $user = User::factory()->create();

        $this->callApiWithLoggedUser($user)
            ->deleteJson(route('useravatar.destroy', ['user' => $user->uuid]))
            ->assertNoContent();
    }

    public function testStoreRequiresAuthentication(): void
    {
        $user = User::factory()->create();

        $this->getJson(route('useravatar.show', ['user' => $user->uuid]))
            ->assertUnauthorized();
    }
}
