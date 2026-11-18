<?php

namespace App\Services;

use App\Models\ScheduleRule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of ScheduleRuleServiceInterface
 */
interface ScheduleRuleServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getMyRules(): LengthAwarePaginator;

    /**
     * @param ScheduleRule $rule
     * @return ScheduleRule
     *
     * @throws ModelNotFoundException
     */
    public function getMyRule(ScheduleRule $rule): ScheduleRule;

    /**
     * @param array $data
     * @return ScheduleRule
     */
    public function createRule(array $data): ScheduleRule;

    /**
     * @param ScheduleRule $rule
     * @param array $data
     * @return ScheduleRule
     *
     * @throws ModelNotFoundException
     */
    public function updateRule(ScheduleRule $rule, array $data): ScheduleRule;

    /**
     * @param ScheduleRule $rule
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function deleteRule(ScheduleRule $rule): void;
}
