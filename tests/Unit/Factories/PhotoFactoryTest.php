<?php

namespace Tests\Unit\Factories;

use App\Models\Photo;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PhotoFactoryTest
 */
final class PhotoFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPhotoCreateByFactory(): void
    {
        $photo = Photo::factory()->create(['filename' => 'rtg-test']);

        $this->assertEquals('rtg-test', $photo->filename);
    }
}
