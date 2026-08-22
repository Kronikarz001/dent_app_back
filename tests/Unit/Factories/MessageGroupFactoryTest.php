<?php

namespace Tests\Unit\Factories;

use App\Models\MessageGroup;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MessageGroupFactoryTest
 */
final class MessageGroupFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testMessageGroupCreateByFactory(): void
    {
        $messageGroup = MessageGroup::factory()->create(['name' => 'Grupa testowa']);

        $this->assertEquals('Grupa testowa', $messageGroup->name);
    }
}
