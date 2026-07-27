<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of DefaultMessageGroupException
 */
class DefaultMessageGroupException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Nie można edytować grupy domyślnej.')
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
