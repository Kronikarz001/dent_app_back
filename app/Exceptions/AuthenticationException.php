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
    public function __construct()
    {
        parent::__construct(__('bad.credentials'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return ErrorHandlerResource::make($this)->response()->setStatusCode(code: 401);
    }
}
