<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\DictionaryGenerationException;
use Tests\Unit\UnitTestCase;

/**
 * Summary of DictionaryGenerationExceptionTest
 */
final class DictionaryGenerationExceptionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testKeepsMessageAndHasNoHttpRendering(): void
    {
        $exception = new DictionaryGenerationException('Nie udało się zapisać słownika.');

        $this->assertSame('Nie udało się zapisać słownika.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertFalse(method_exists($exception, 'render'));
    }
}
