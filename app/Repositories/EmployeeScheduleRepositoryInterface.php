<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;

/**
 * Summary of EmployeeScheduleRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<EmployeeSchedule>
 */
interface EmployeeScheduleRepositoryInterface extends ScheduleOverlapRepositoryInterface, SearchableRepositoryInterface {}
