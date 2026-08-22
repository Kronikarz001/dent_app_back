<?php

namespace App\Exceptions;

use Exception;

/**
 * Summary of DictionaryGenerationException
 */
class DictionaryGenerationException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
