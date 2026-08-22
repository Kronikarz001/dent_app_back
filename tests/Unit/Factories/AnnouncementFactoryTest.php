<?php

namespace Tests\Unit\Factories;

use App\Models\Announcement;
use Tests\Unit\UnitTestCase;

/**
 * Summary of AnnouncementFactoryTest
 */
final class AnnouncementFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testAnnouncementCreateByFactory(): void
    {
        $announcement = Announcement::factory()->create(['title' => 'Tytuł testowy']);

        $this->assertEquals('Tytuł testowy', $announcement->title);
    }
}
