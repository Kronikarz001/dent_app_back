<?php

namespace Tests\Unit\Models;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AnnouncementTest
 */
final class AnnouncementTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPublishedAtIsCastToDate(): void
    {
        $announcement = Announcement::factory()->create(['published_at' => '2026-01-15']);

        $this->assertInstanceOf(Carbon::class, $announcement->published_at);
    }

    /**
     * @return void
     */
    public function testAuthorReturnsAssignedUser(): void
    {
        $author = User::factory()->create();
        $announcement = Announcement::factory()->create(['user_uuid' => $author->uuid]);

        $this->assertTrue($announcement->author->is($author));
    }

    /**
     * @return void
     */
    public function testAuthorIsNullWhenNotAssigned(): void
    {
        $announcement = Announcement::factory()->create(['user_uuid' => null]);

        $this->assertNull($announcement->author);
    }
}
