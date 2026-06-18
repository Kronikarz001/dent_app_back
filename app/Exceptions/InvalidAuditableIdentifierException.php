<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of InvalidAuditableIdentifierException
 */
class InvalidAuditableIdentifierException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Invalid auditable identifier')
    {
        parent::__construct($message, 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return ErrorHandlerResource::make($this)->response()->setStatusCode($this->getCode());
    }
}
