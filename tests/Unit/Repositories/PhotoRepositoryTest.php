<?php

namespace Tests\Unit\Repositories;

use App\Models\Photo;
use App\Repositories\PhotoRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of PhotoRepositoryTest
 */
class PhotoRepositoryTest extends TestCase
{
    /**
     * @var PhotoRepositoryInterface
     */
    private PhotoRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PhotoRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsPhoto(): void
    {
        $photo = $this->repository->create(Photo::factory()->make()->toArray());

        $this->assertDatabaseHas('photos', ['uuid' => $photo->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateChangesPhotoAttributes(): void
    {
        $photo = Photo::factory()->create();

        $updated = $this->repository->update($photo, ['filename' => 'zmieniony']);

        $this->assertSame('zmieniony', $updated->filename);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesPhoto(): void
    {
        $photo = Photo::factory()->create();

        $this->assertTrue($this->repository->delete($photo));
        $this->assertDatabaseMissing('photos', ['uuid' => $photo->uuid]);
    }
}
