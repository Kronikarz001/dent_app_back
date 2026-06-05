<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/user-info', [UserController::class, 'showLoggedUser'])
        ->name('user.user-info');
    Route::get('/user/export', [UserController::class, 'export'])
        ->name('user.export');
    Route::get('/user/selectlist', [UserController::class, 'selectList'])
        ->name('user.selectList');
    Route::apiResource('/user', UserController::class)
        ->names('user');

    Route::apiResource('/user/{user}/file', UserFileController::class)
        ->names('userfile');
    Route::get('/user/{user}/file-download/{file}', [UserFileController::class, 'download'])
        ->name('userfile.download');
    Route::post('/user/{user}/file-new-version/{file}', [UserFileController::class, 'storeNewVersion'])
        ->name('userfile.newversion');

    Route::post('/user/{user}/avatar', [UserFileController::class, 'storeAvatar'])
        ->name('userfile.avatar-store');
    Route::get('/user/{user}/file-avatar-download/{file}', [UserFileController::class, 'avatarDownload'])
        ->name('userfile.avatar-download');

    Route::post('/user/{user}/background', [UserFileController::class, 'storeBackground'])
        ->name('userfile.background-store');
    Route::get('/user/{user}/file-background-download/{file}', [UserFileController::class, 'backgroundDownload'])
        ->name('userfile.background-download');
});
