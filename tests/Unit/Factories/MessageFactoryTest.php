<?php

namespace Tests\Unit\Factories;

use App\Models\Message;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MessageFactoryTest
 */
final class MessageFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testMessageCreateByFactory(): void
    {
        $message = Message::factory()->create(['message' => 'Treść testowa']);

        $this->assertEquals('Treść testowa', $message->message);
    }
}
