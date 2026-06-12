<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Shared\File as SpreadsheetFile;

/**
 * Summary of AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(SearchServiceProvider::class);
        $this->app->register(UserServiceProvider::class);
        $this->app->register(PatientServiceProvider::class);
        $this->app->register(JobPositionServiceProvider::class);
        $this->app->register(CalendarServiceProvider::class);
        $this->app->register(FileServiceProvider::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        ini_set('upload_tmp_dir', storage_path('temp'));
        SpreadsheetFile::setUseUploadTempDirectory(true);
    }
}
