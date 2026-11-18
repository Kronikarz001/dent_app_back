<?php

namespace App\Services;

use App\Models\ScheduleRule;
use App\Repositories\ScheduleRuleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of ScheduleRuleService
 */
readonly class ScheduleRuleService implements ScheduleRuleServiceInterface
{
    /**
     * @param ScheduleRuleRepositoryInterface $scheduleRuleRepository
     */
    public function __construct(
        private ScheduleRuleRepositoryInterface $scheduleRuleRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getMyRules(): LengthAwarePaginator
    {
        return $this->scheduleRuleRepository->findAllWithPagination(['user_uuid' => Auth::user()->uuid]);
    }

    /**
     * @param ScheduleRule $rule
     * @return ScheduleRule
     *
     * @throws ModelNotFoundException
     */
    public function getMyRule(ScheduleRule $rule): ScheduleRule
    {
        $this->assertOwnedByCurrentUser($rule);

        return $rule;
    }

    /**
     * @param array $data
     * @return ScheduleRule
     */
    public function createRule(array $data): ScheduleRule
    {
        return $this->scheduleRuleRepository->create(array_merge($data, [
            'user_uuid' => Auth::user()->uuid,
        ]));
    }

    /**
     * @param ScheduleRule $rule
     * @param array $data
     * @return ScheduleRule
     *
     * @throws ModelNotFoundException
     */
    public function updateRule(ScheduleRule $rule, array $data): ScheduleRule
    {
        $this->assertOwnedByCurrentUser($rule);

        return $this->scheduleRuleRepository->update($rule, $data);
    }

    /**
     * @param ScheduleRule $rule
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function deleteRule(ScheduleRule $rule): void
    {
        $this->assertOwnedByCurrentUser($rule);

        $this->scheduleRuleRepository->delete($rule);
    }

    /**
     * @param ScheduleRule $rule
     * @return void
     *
     * @throws ModelNotFoundException
     */
    private function assertOwnedByCurrentUser(ScheduleRule $rule): void
    {
        if ($rule->user_uuid !== Auth::user()->uuid) {
            throw (new ModelNotFoundException)->setModel(ScheduleRule::class, [$rule->uuid]);
        }
    }
}
