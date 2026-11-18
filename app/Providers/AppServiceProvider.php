<?php

namespace App\Providers;

use App\Models\User;
use App\Notifications\Channels\DatabaseChannel;
use App\Observers\UserObserver;
use App\Repositories;
use App\Services;
use Carbon\CarbonInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Shared\File as SpreadsheetFile;

/**
 * Summary of AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        Repositories\AnnouncementRepositoryInterface::class => Repositories\AnnouncementRepository::class,
        Repositories\AuditRepositoryInterface::class => Repositories\AuditRepository::class,
        Repositories\CalendarRepositoryInterface::class => Repositories\CalendarRepository::class,
        Repositories\CompanyRepositoryInterface::class => Repositories\CompanyRepository::class,
        Repositories\DentalExaminationRepositoryInterface::class => Repositories\DentalExaminationRepository::class,
        Repositories\DentistRepositoryInterface::class => Repositories\DentistRepository::class,
        Repositories\DepartmentRepositoryInterface::class => Repositories\DepartmentRepository::class,
        Repositories\EmployeeScheduleRepositoryInterface::class => Repositories\EmployeeScheduleRepository::class,
        Repositories\FileRepositoryInterface::class => Repositories\FileRepository::class,
        Repositories\JobPositionRepositoryInterface::class => Repositories\JobPositionRepository::class,
        Repositories\MaterialRepositoryInterface::class => Repositories\MaterialRepository::class,
        Repositories\MedicineRepositoryInterface::class => Repositories\MedicineRepository::class,
        Repositories\MessageGroupRepositoryInterface::class => Repositories\MessageGroupRepository::class,
        Repositories\MessageRepositoryInterface::class => Repositories\MessageRepository::class,
        Repositories\PatientRepositoryInterface::class => Repositories\PatientRepository::class,
        Repositories\PermissionGroupRepositoryInterface::class => Repositories\PermissionGroupRepository::class,
        Repositories\PermissionRepositoryInterface::class => Repositories\PermissionRepository::class,
        Repositories\PhoneNumberRepositoryInterface::class => Repositories\PhoneNumberRepository::class,
        Repositories\PhotoRepositoryInterface::class => Repositories\PhotoRepository::class,
        Repositories\PrescriptionRepositoryInterface::class => Repositories\PrescriptionRepository::class,
        Repositories\RoleRepositoryInterface::class => Repositories\RoleRepository::class,
        Repositories\RoomRepositoryInterface::class => Repositories\RoomRepository::class,
        Repositories\ScheduleRuleRepositoryInterface::class => Repositories\ScheduleRuleRepository::class,
        Repositories\SearchRepositoryInterface::class => Repositories\SearchRepository::class,
        Repositories\UserGroupRepositoryInterface::class => Repositories\UserGroupRepository::class,
        Repositories\UserRepositoryInterface::class => Repositories\UserRepository::class,

        Services\AnnouncementServiceInterface::class => Services\AnnouncementService::class,
        Services\AuditServiceInterface::class => Services\AuditService::class,
        Services\AuthServiceInterface::class => Services\AuthService::class,
        Services\CalendarServiceInterface::class => Services\CalendarService::class,
        Services\CompanyServiceInterface::class => Services\CompanyService::class,
        Services\DentalExaminationServiceInterface::class => Services\DentalExaminationService::class,
        Services\DentistServiceInterface::class => Services\DentistService::class,
        Services\DepartmentServiceInterface::class => Services\DepartmentService::class,
        Services\DictionaryServiceInterface::class => Services\DictionaryService::class,
        Services\EmployeeScheduleServiceInterface::class => Services\EmployeeScheduleService::class,
        Services\ExportServiceInterface::class => Services\ExportService::class,
        Services\FileServiceInterface::class => Services\FileService::class,
        Services\JobPositionServiceInterface::class => Services\JobPositionService::class,
        Services\MaterialServiceInterface::class => Services\MaterialService::class,
        Services\MedicineServiceInterface::class => Services\MedicineService::class,
        Services\MessageGroupServiceInterface::class => Services\MessageGroupService::class,
        Services\MessageServiceInterface::class => Services\MessageService::class,
        Services\NotificationServiceInterface::class => Services\NotificationService::class,
        Services\P1ServiceInterface::class => Services\P1Service::class,
        Services\PatientServiceInterface::class => Services\PatientService::class,
        Services\PermissionGrantServiceInterface::class => Services\PermissionGrantService::class,
        Services\PermissionGroupServiceInterface::class => Services\PermissionGroupService::class,
        Services\PermissionServiceInterface::class => Services\PermissionService::class,
        Services\PhoneNumberServiceInterface::class => Services\PhoneNumberService::class,
        Services\PhotoServiceInterface::class => Services\PhotoService::class,
        Services\PrescriptionServiceInterface::class => Services\PrescriptionService::class,
        Services\RoleServiceInterface::class => Services\RoleService::class,
        Services\RoomServiceInterface::class => Services\RoomService::class,
        Services\ScheduleRuleServiceInterface::class => Services\ScheduleRuleService::class,
        Services\SearchServiceInterface::class => Services\SearchService::class,
        Services\StatisticsServiceInterface::class => Services\StatisticsService::class,
        Services\UserGroupServiceInterface::class => Services\UserGroupService::class,
        Services\UserServiceInterface::class => Services\UserService::class,
    ];

    /**
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Date::serializeUsing(fn (CarbonInterface $date) => $date->format('Y-m-d\TH:i:s.u'));
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        ini_set('upload_tmp_dir', storage_path('temp'));
        SpreadsheetFile::setUseUploadTempDirectory(true);

        $this->callAfterResolving(ChannelManager::class, function (ChannelManager $manager) {
            $manager->extend('database', fn () => new DatabaseChannel);
        });

        User::observe(UserObserver::class);
    }
}
