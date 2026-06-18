<?php

namespace Tests\Feature\Controllers;

use App\Models\Calendar;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Summary of CalendarFileControllerTest
 */
class CalendarFileControllerTest extends TestCase
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
        $calendar = Calendar::factory()->create();
        File::factory()->create([
            'fileable_type' => Calendar::class,
            'fileable_id' => $calendar->uuid,
            'is_latest' => true,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendarfile.index', ['calendar' => $calendar->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * Files are tagged with the model's actual morph class (Calendar), so they do
     * show up in index() despite store() passing FileableType::JOB_POSITION (which
     * only affects the storage directory prefix, see testStoreUploadsFileAndPersistsRecord).
     *
     * @return void
     */
    public function testFilesUploadedViaStoreAreReturnedByIndex(): void
    {
        $calendar = Calendar::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), [
                'files' => [$upload],
            ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendarfile.index', ['calendar' => $calendar->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /**
     * @return void
     */
    public function testStoreUploadsFileAndPersistsRecord(): void
    {
        $calendar = Calendar::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 10, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), [
                'files' => [$upload],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.filename', 'document');

        $this->assertDatabaseHas('files', [
            'fileable_id' => $calendar->uuid,
            'filename' => 'document',
            'is_latest' => true,
        ]);

        $file = File::query()->where('fileable_id', $calendar->uuid)->first();
        $this->assertStringStartsWith('job_position/', $file->path);
    }

    /**
     * @return void
     */
    public function testStoreWithoutFilesReturnsValidationError(): void
    {
        $calendar = Calendar::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), []);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testShowReturnsFileMetadata(): void
    {
        $calendar = Calendar::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => Calendar::class,
            'fileable_id' => $calendar->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendarfile.show', ['calendar' => $calendar->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $file->uuid);
    }

    /**
     * @return void
     */
    public function testDownloadReturnsOriginalFileContent(): void
    {
        $calendar = Calendar::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $calendar->uuid)->first();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendarfile.download', ['calendar' => $calendar->uuid, 'file' => $file->uuid]));

        $response->assertOk();
        $this->assertSame('document', $response->json('filename'));
        $this->assertSame(file_get_contents($upload->getPathname()), base64_decode($response->json('content')));
    }

    /**
     * @return void
     */
    public function testDownloadMissingPhysicalFileReturnsNotFound(): void
    {
        $calendar = Calendar::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => Calendar::class,
            'fileable_id' => $calendar->uuid,
            'path' => 'job_position/does/not/exist.pdf',
        ]);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('calendarfile.download', ['calendar' => $calendar->uuid, 'file' => $file->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testUpdateRenamesFile(): void
    {
        $calendar = Calendar::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => Calendar::class,
            'fileable_id' => $calendar->uuid,
        ]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('calendarfile.update', ['calendar' => $calendar->uuid, 'file' => $file->uuid]), [
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
        $calendar = Calendar::factory()->create();
        $upload = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), [
                'files' => [$upload],
            ]);

        $file = File::query()->where('fileable_id', $calendar->uuid)->first();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('calendarfile.destroy', ['calendar' => $calendar->uuid, 'file' => $file->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('files', ['uuid' => $file->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreNewVersionMarksPreviousVersionAsNotLatest(): void
    {
        $calendar = Calendar::factory()->create();
        $original = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

        $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.store', ['calendar' => $calendar->uuid]), [
                'files' => [$original],
            ]);

        $firstVersion = File::query()->where('fileable_id', $calendar->uuid)->first();
        $newVersion = UploadedFile::fake()->create('document.pdf', 2, 'application/pdf');

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('calendarfile.newversion', ['calendar' => $calendar->uuid, 'file' => $firstVersion->uuid]), [
                'files' => [$newVersion],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('files', [
            'uuid' => $firstVersion->uuid,
            'is_latest' => false,
        ]);
    }
}
