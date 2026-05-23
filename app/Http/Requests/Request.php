<?php

namespace App\Http\Requests;

use App\Dto\Dto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of Request
 */
abstract class Request extends FormRequest
{
    /**
     * @return Dto
     */
    abstract public function getDto(): Dto;
}
