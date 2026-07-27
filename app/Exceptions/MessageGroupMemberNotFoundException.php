<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of MessageGroupMemberNotFoundException
 */
class MessageGroupMemberNotFoundException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Użytkownik nie jest członkiem tej grupy.')
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
