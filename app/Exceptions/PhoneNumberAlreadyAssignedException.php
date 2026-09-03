<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorHandlerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Summary of PhoneNumberAlreadyAssignedException
 */
class PhoneNumberAlreadyAssignedException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message = 'Ten numer telefonu jest już przypisany do innego użytkownika lub pacjenta.')
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
