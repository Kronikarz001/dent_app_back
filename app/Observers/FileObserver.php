<?php

namespace App\Observers;

use App\Models\File;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class FileObserver
{
    /**
     * @param File $file
     * @return void
     */
    public function creating(File $file): void
    {
        if (Auth::check()) {
            $file->user_id = UserService::getLoggedUser()->id;
        }
    }
}
