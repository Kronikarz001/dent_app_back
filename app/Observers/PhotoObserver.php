<?php

namespace App\Observers;

use App\Models\Photo;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of PhotoObserver
 */
class PhotoObserver
{
    /**
     * @param Photo $photo
     * @return void
     */
    public function creating(Photo $photo): void
    {
        if (Auth::check()) {
            $photo->user_uuid = Auth::user()->uuid;
        }
    }
}
