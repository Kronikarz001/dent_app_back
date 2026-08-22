<?php

namespace Tests\Unit\Factories;

use App\Models\File;
use Tests\Unit\UnitTestCase;

/**
 * Summary of FileFactoryTest
 */
final class FileFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFileCreateByFactory(): void
    {
        $file = File::factory()->create(['filename' => 'dokument']);

        $this->assertEquals('dokument', $file->filename);
    }
}
