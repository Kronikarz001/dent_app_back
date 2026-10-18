<?php

namespace Tests\Unit\Repositories;

use App\Models\Announcement;
use App\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of AnnouncementRepositoryTest
 */
class AnnouncementRepositoryTest extends TestCase
{
    /**
     * @var AnnouncementRepositoryInterface
     */
    private AnnouncementRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AnnouncementRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsAnnouncement(): void
    {
        $announcement = $this->repository->create([
            'title' => 'Test',
            'content' => 'Treść',
            'published_at' => '2026-01-01',
        ]);

        $this->assertDatabaseHas('announcements', ['uuid' => $announcement->uuid, 'title' => 'Test']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingAnnouncement(): void
    {
        $announcement = Announcement::factory()->create();

        $found = $this->repository->findByUuid($announcement->uuid);

        $this->assertTrue($found->is($announcement));
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repository->findByUuid((string) Str::uuid()));
    }

    /**
     * @return void
     */
    public function testUpdateChangesAnnouncementAttributes(): void
    {
        $announcement = Announcement::factory()->create();

        $updated = $this->repository->update($announcement, ['title' => 'Zmieniony']);

        $this->assertSame('Zmieniony', $updated->title);
        $this->assertDatabaseHas('announcements', ['uuid' => $announcement->uuid, 'title' => 'Zmieniony']);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesAnnouncement(): void
    {
        $announcement = Announcement::factory()->create();

        $this->assertTrue($this->repository->delete($announcement));
        $this->assertDatabaseMissing('announcements', ['uuid' => $announcement->uuid]);
    }
}
