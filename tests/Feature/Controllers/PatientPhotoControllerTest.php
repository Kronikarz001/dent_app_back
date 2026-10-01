<?php

namespace Tests\Feature\Controllers;

use App\Models\Patient;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of PatientPhotoControllerTest
 */
class PatientPhotoControllerTest extends TestCase
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
        Photo::factory()->create([
            'patient_uuid' => $patient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientphoto.index', ['patient' => $patient->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testShowRejectsPhotoBelongingToAnotherPatient(): void
    {
        $ownPatient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();
        $foreignPhoto = Photo::factory()->create([
            'patient_uuid' => $otherPatient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientphoto.show', ['patient' => $ownPatient->uuid, 'photo' => $foreignPhoto->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStoreUploadsPhotoAndPersistsRecord(): void
    {
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->image('xray.jpg');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patientphoto.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'xray');

        $this->assertDatabaseHas('photos', [
            'patient_uuid' => $patient->uuid,
            'filename' => 'xray',
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patientphoto.store', ['patient' => $patient->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsPhotoMetadata(): void
    {
        $patient = Patient::factory()->create();
        $photo = Photo::factory()->create([
            'patient_uuid' => $patient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientphoto.show', ['patient' => $patient->uuid, 'photo' => $photo->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $photo->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalPhotoContent(): void
    {
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->image('xray.jpg');

        $this->callApiWithLoggedUser()
            ->postJson(route('patientphoto.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $photo = Photo::query()->where('patient_uuid', $patient->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientphoto.download', ['patient' => $patient->uuid, 'photo' => $photo->uuid]));

        $response->assertOk();
        $this->assertSame('xray', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalPhotoReturnsNotFound(): void
    {
        $patient = Patient::factory()->create();
        $photo = Photo::factory()->create([
            'patient_uuid' => $patient->uuid,
            'path' => 'photo/does/not/exist.jpg',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patientphoto.download', ['patient' => $patient->uuid, 'photo' => $photo->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesPhoto(): void
    {
        $patient = Patient::factory()->create();
        $photo = Photo::factory()->create([
            'patient_uuid' => $patient->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patientphoto.update', ['patient' => $patient->uuid, 'photo' => $photo->uuid]), [
                'filename' => 'renamed',
            ]);

        $response->assertOk();
        $response->assertJsonPath('filename', 'renamed');

        $this->assertDatabaseHas('photos', [
            'uuid' => $photo->uuid,
            'filename' => 'renamed',
        ]);
    }

    /**
     * @return void
     */
    public function testDestroyRemovesPhoto(): void
    {
        $patient = Patient::factory()->create();
        $upload = UploadedFile::fake()->image('xray.jpg');

        $this->callApiWithLoggedUser()
            ->postJson(route('patientphoto.store', ['patient' => $patient->uuid]), [
                'files' => [$upload],
            ]);

        $photo = Photo::query()->where('patient_uuid', $patient->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('patientphoto.destroy', ['patient' => $patient->uuid, 'photo' => $photo->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('photos', ['uuid' => $photo->uuid]);
    }
}
