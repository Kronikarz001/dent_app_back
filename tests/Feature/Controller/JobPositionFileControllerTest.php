<?php

namespace Tests\Feature\Controller;

use App\Models\File;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobPositionFileControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.index', ['jobPosition' => $jobPosition->uuid]))
            ->assertOk();
    }

    public function testStoreReturnsCreatedResponse(): void
    {
        Storage::fake('files');

        $jobPosition = JobPosition::factory()->create();
        $file        = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->post(route('jobpositionfile.store', ['jobPosition' => $jobPosition->uuid]), [
                'files' => [$file],
            ])
            ->assertCreated();
    }

    public function testShowReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user        = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $path        = 'job_position/ab/cd/ef/abcdef/file';
        $fileModel   = File::factory()->create([
            'path'          => $path,
            'fileable_id'   => $jobPosition->uuid,
            'fileable_type' => JobPosition::class,
            'user_uuid'     => $user->uuid,
        ]);

        Storage::disk('local')->put($path, Crypt::encrypt('test content'));

        $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.show', ['jobPosition' => $jobPosition->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testUpdateReturnsSuccessResponse(): void
    {
        $user        = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $fileModel   = File::factory()->create([
            'fileable_id'   => $jobPosition->uuid,
            'fileable_type' => JobPosition::class,
            'user_uuid'     => $user->uuid,
        ]);

        $this->callApiWithLoggedUser()
            ->putJson(route('jobpositionfile.update', ['jobPosition' => $jobPosition->uuid, 'file' => $fileModel->uuid]), [
                'filename' => 'new_name',
            ])
            ->assertOk();
    }

    public function testDestroyReturnsNoContentResponse(): void
    {
        Storage::fake('files');

        $user        = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $path        = 'job_position/ab/cd/ef/abcdef/file';
        $fileModel   = File::factory()->create([
            'path'          => $path,
            'fileable_id'   => $jobPosition->uuid,
            'fileable_type' => JobPosition::class,
            'user_uuid'     => $user->uuid,
        ]);

        Storage::disk('local')->put($path, Crypt::encrypt('test'));

        $this->callApiWithLoggedUser()
            ->deleteJson(route('jobpositionfile.destroy', ['jobPosition' => $jobPosition->uuid, 'file' => $fileModel->uuid]))
            ->assertNoContent();
    }

    public function testDownloadReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user        = User::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $path        = 'job_position/ab/cd/ef/abcdef/file';
        $fileModel   = File::factory()->create([
            'path'          => $path,
            'fileable_id'   => $jobPosition->uuid,
            'fileable_type' => JobPosition::class,
            'user_uuid'     => $user->uuid,
        ]);

        Storage::disk('local')->put($path, Crypt::encrypt('test content'));

        $this->callApiWithLoggedUser()
            ->getJson(route('jobpositionfile.download', ['jobPosition' => $jobPosition->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testStoreNewVersionReturnsCreatedResponse(): void
    {
        Storage::fake('files');

        $user         = User::factory()->create();
        $jobPosition  = JobPosition::factory()->create();
        $path         = 'job_position/ab/cd/ef/abcdef/old';
        $existingFile = File::factory()->create([
            'path'          => $path,
            'fileable_id'   => $jobPosition->uuid,
            'fileable_type' => JobPosition::class,
            'user_uuid'     => $user->uuid,
            'is_latest'     => true,
        ]);

        Storage::disk('local')->put($path, Crypt::encrypt('old content'));

        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->post(route('jobpositionfile.newversion', ['jobPosition' => $jobPosition->uuid, 'file' => $existingFile->uuid]), [
                'files' => [$newFile],
            ])
            ->assertCreated();
    }

    public function testIndexRequiresAuthentication(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $this->getJson(route('jobpositionfile.index', ['jobPosition' => $jobPosition->uuid]))
            ->assertUnauthorized();
    }
}
