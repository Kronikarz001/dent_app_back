<?php

namespace App\Enums;

use Illuminate\Http\Request;

/**
 * Summary of PermissionType
 */
enum PermissionType: string
{
    case VIEW = 'view';
    case EDIT = 'edit';

    /**
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return $request->isMethod('get') ? self::VIEW : self::EDIT;
    }
}
