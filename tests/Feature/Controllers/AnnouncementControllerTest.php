<?php

namespace Tests\Feature\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of AnnouncementControllerTest
 */
class AnnouncementControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsSuccessResponse(): void
    {
        Announcement::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('announcement.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowAnnouncementReturnsSuccessResponse(): void
    {
        $announcement = Announcement::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('announcement.show', ['announcement' => $announcement->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $announcement->uuid);
    }

    /**
     * @return void
     */
    public function testStoreAnnouncementReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('announcement.store'), [
                'title' => 'Spotkanie zespołu',
                'content' => 'Spotkanie odbędzie się o 10:00.',
                'published_at' => '2026-12-31',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('announcements', ['title' => 'Spotkanie zespołu']);
    }

    /**
     * @return void
     */
    public function testStoreAnnouncementSetsAuthorToLoggedUser(): void
    {
        $user = User::factory()->create();

        $this->callApiWithLoggedUser($user)
            ->postJson(route('announcement.store'), [
                'title' => 'Spotkanie',
                'content' => 'Treść',
                'published_at' => '2026-12-31',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('announcements', ['title' => 'Spotkanie', 'user_uuid' => $user->uuid]);
    }

    /**
     * @return void
     */
    public function testStoreAnnouncementWithoutTitleReturnsValidationError(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('announcement.store'), [
                'content' => 'Treść',
                'published_at' => '2026-12-31',
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateAnnouncementReturnsSuccessResponse(): void
    {
        $announcement = Announcement::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('announcement.update', ['announcement' => $announcement->uuid]), [
                'title' => 'Zaktualizowany tytuł',
                'content' => $announcement->content,
                'published_at' => $announcement->published_at->format('Y-m-d'),
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('announcements', ['uuid' => $announcement->uuid, 'title' => 'Zaktualizowany tytuł']);
    }

    /**
     * @return void
     */
    public function testDestroyAnnouncementReturnsNoContentResponse(): void
    {
        $announcement = Announcement::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('announcement.destroy', ['announcement' => $announcement->uuid]))
            ->assertNoContent();

        $this->assertModelMissing($announcement);
    }
}
