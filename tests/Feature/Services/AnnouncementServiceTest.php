<?php

namespace Tests\Feature\Services;

use App\Models\Announcement;
use App\Models\User;
use App\Services\AnnouncementServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Summary of AnnouncementServiceTest
 */
class AnnouncementServiceTest extends TestCase
{
    /**
     * @var AnnouncementServiceInterface|Application|mixed|object
     */
    private AnnouncementServiceInterface $service;

    protected const ANNOUNCEMENTS_TABLE = 'announcements';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AnnouncementServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetAllReturnsPaginatedResults(): void
    {
        Announcement::factory()->count(3)->create();

        $result = $this->service->getAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testCreateSetsAuthorToAuthenticatedUser(): void
    {
        $user = User::factory()->create();
        Auth::setUser($user);

        $announcement = $this->service->create([
            'title' => 'Tytuł',
            'content' => 'Treść',
            'published_at' => '2026-12-31',
        ]);

        $this->assertInstanceOf(Announcement::class, $announcement);
        $this->assertDatabaseHas(self::ANNOUNCEMENTS_TABLE, ['uuid' => $announcement->uuid, 'user_uuid' => $user->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdatePersistsChangesToDatabase(): void
    {
        $announcement = Announcement::factory()->create();

        $result = $this->service->update($announcement, ['title' => 'Nowy tytuł']);

        $this->assertInstanceOf(Announcement::class, $result);
        $this->assertDatabaseHas(self::ANNOUNCEMENTS_TABLE, ['uuid' => $announcement->uuid, 'title' => 'Nowy tytuł']);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesFromDatabase(): void
    {
        $announcement = Announcement::factory()->create();

        $this->service->delete($announcement);

        $this->assertDatabaseMissing(self::ANNOUNCEMENTS_TABLE, ['uuid' => $announcement->uuid]);
    }
}
