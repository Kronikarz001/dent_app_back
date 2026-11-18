<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Illuminate\Auth\Access\AuthorizationException as Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of AuthenticationException
 */
class AuthenticationException extends Exception
{
    /**
     * @param string $message
     * @return void
     */
    public function __construct(string $message = 'Nieprawidłowy login lub hasło.')
    {
        parent::__construct($message, 401);
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
