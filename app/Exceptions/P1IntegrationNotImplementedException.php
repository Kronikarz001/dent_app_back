<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Summary of P1IntegrationNotImplementedException
 */
class P1IntegrationNotImplementedException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = 'Integracja z P1 nie jest jeszcze podłączona.', int $code = 501)
    {
        parent::__construct($message, $code);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::warning($this->getMessage());
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return ErrorHandlerResource::make($this)->response()->setStatusCode($this->getCode());
    }
}
