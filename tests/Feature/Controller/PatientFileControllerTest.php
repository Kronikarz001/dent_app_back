<?php

namespace Tests\Feature\Controller;

use App\Models\File;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientFileControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse(): void
    {
        $patient = Patient::factory()->create();

        $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.index', ['patient' => $patient->uuid]))
            ->assertOk();
    }

    public function testStoreReturnsCreatedResponse(): void
    {
        Storage::fake('files');

        $patient = Patient::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->post(route('patientfile.store', ['patient' => $patient->uuid]), [
                'files' => [$file],
            ])
            ->assertCreated();
    }

    public function testShowReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $path = 'patient/ab/cd/ef/abcdef/file';
        $fileModel = File::factory()->create([
            'path' => $path,
            'fileable_id' => $patient->uuid,
            'fileable_type' => Patient::class,
            'user_uuid' => $user->uuid,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('test content'));

        $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.show', ['patient' => $patient->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testUpdateReturnsSuccessResponse(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $fileModel = File::factory()->create([
            'fileable_id' => $patient->uuid,
            'fileable_type' => Patient::class,
            'user_uuid' => $user->uuid,
        ]);

        $this->callApiWithLoggedUser()
            ->putJson(route('patientfile.update', ['patient' => $patient->uuid, 'file' => $fileModel->uuid]), [
                'filename' => 'new_name',
            ])
            ->assertOk();
    }

    public function testDestroyReturnsNoContentResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $path = 'patient/ab/cd/ef/abcdef/file';
        $fileModel = File::factory()->create([
            'path' => $path,
            'fileable_id' => $patient->uuid,
            'fileable_type' => Patient::class,
            'user_uuid' => $user->uuid,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('test'));

        $this->callApiWithLoggedUser()
            ->deleteJson(route('patientfile.destroy', ['patient' => $patient->uuid, 'file' => $fileModel->uuid]))
            ->assertNoContent();
    }

    public function testDownloadReturnsSuccessResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $path = 'patient/ab/cd/ef/abcdef/file';
        $fileModel = File::factory()->create([
            'path' => $path,
            'fileable_id' => $patient->uuid,
            'fileable_type' => Patient::class,
            'user_uuid' => $user->uuid,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('test content'));

        $this->callApiWithLoggedUser()
            ->getJson(route('patientfile.download', ['patient' => $patient->uuid, 'file' => $fileModel->uuid]))
            ->assertOk();
    }

    public function testStoreNewVersionReturnsCreatedResponse(): void
    {
        Storage::fake('files');

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $path = 'patient/ab/cd/ef/abcdef/old';
        $existingFile = File::factory()->create([
            'path' => $path,
            'fileable_id' => $patient->uuid,
            'fileable_type' => Patient::class,
            'user_uuid' => $user->uuid,
            'is_latest' => true,
        ]);

        Storage::disk('files')->put($path, Crypt::encrypt('old content'));

        $newFile = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->post(route('patientfile.newversion', ['patient' => $patient->uuid, 'file' => $existingFile->uuid]), [
                'files' => [$newFile],
            ])
            ->assertCreated();
    }

    public function testIndexRequiresAuthentication(): void
    {
        $patient = Patient::factory()->create();

        $this->getJson(route('patientfile.index', ['patient' => $patient->uuid]))
            ->assertUnauthorized();
    }
}
