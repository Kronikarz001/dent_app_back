<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of ScheduleConflictException
 */
class ScheduleConflictException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Wybrany termin koliduje z już istniejącym wpisem w kalendarzu.')
    {
        parent::__construct($message, 409);
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
