<?php

namespace App\Observers;

use App\Models\File;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of FileObserver
 */
class FileObserver
{
    /**
     * @param File $file
     * @return void
     */
    public function creating(File $file): void
    {
        if (Auth::check()) {
            $file->user_uuid = Auth::user()->uuid;
        }
    }
}
