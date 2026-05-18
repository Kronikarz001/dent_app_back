<?php

use App\Http\Controllers\PublicPhotoController;

Route::apiResource('public-photo', PublicPhotoController::class)
    ->parameters(['public-photo' => 'file'])
    ->only(['show', 'store', 'update', 'destroy'])
    ->names('public-photo');

require __DIR__ . '/api/auth.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/job-position.php';
require __DIR__ . '/api/patient.php';
