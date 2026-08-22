<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Providers\AppServiceProviders\AnnouncementServiceProvider;
use App\Providers\AppServiceProviders\AuditServiceProvider;
use App\Providers\AppServiceProviders\AuthServiceProvider;
use App\Providers\AppServiceProviders\CalendarServiceProvider;
use App\Providers\AppServiceProviders\CompanyServiceProvider;
use App\Providers\AppServiceProviders\DentalExaminationServiceProvider;
use App\Providers\AppServiceProviders\DentistServiceProvider;
use App\Providers\AppServiceProviders\DictionaryServiceProvider;
use App\Providers\AppServiceProviders\EmployeeScheduleServiceProvider;
use App\Providers\AppServiceProviders\FileServiceProvider;
use App\Providers\AppServiceProviders\JobPositionServiceProvider;
use App\Providers\AppServiceProviders\MaterialServiceProvider;
use App\Providers\AppServiceProviders\MessageServiceProvider;
use App\Providers\AppServiceProviders\NotificationServiceProvider;
use App\Providers\AppServiceProviders\PatientServiceProvider;
use App\Providers\AppServiceProviders\SearchServiceProvider;
use App\Providers\AppServiceProviders\UserServiceProvider;
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
        $this->app->register(AuditServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(SearchServiceProvider::class);
        $this->app->register(UserServiceProvider::class);
        $this->app->register(PatientServiceProvider::class);
        $this->app->register(JobPositionServiceProvider::class);
        $this->app->register(DentistServiceProvider::class);
        $this->app->register(CalendarServiceProvider::class);
        $this->app->register(EmployeeScheduleServiceProvider::class);
        $this->app->register(CompanyServiceProvider::class);
        $this->app->register(FileServiceProvider::class);
        $this->app->register(DentalExaminationServiceProvider::class);
        $this->app->register(MaterialServiceProvider::class);
        $this->app->register(MessageServiceProvider::class);
        $this->app->register(AnnouncementServiceProvider::class);
        $this->app->register(DictionaryServiceProvider::class);
        $this->app->register(NotificationServiceProvider::class);
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

        User::observe(UserObserver::class);
    }
}
