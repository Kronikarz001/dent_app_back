<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of MessageGroupMinimumMembersException
 */
class MessageGroupMinimumMembersException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Grupa musi mieć co najmniej 2 członków.')
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
