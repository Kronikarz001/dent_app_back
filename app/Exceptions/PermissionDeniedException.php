<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of PermissionDeniedException
 */
class PermissionDeniedException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Nie masz uprawnień do wykonania tej akcji.')
    {
        parent::__construct($message, 403);
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
