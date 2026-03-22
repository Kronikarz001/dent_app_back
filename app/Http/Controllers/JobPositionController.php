<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobPositionRequest;
use App\Models\JobPosition;
use App\Resources\JobPositionResource;
use App\Services\JobPositionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of JobPositionController
 */
class JobPositionController extends Controller
{
    /**
     * @param JobPositionServiceInterface $jobPositionService
     */
    public function __construct(
        private readonly JobPositionServiceInterface $jobPositionService
    ) {
    }

    /**
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositions();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function indexList(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositionsList();
    }

    /**
     * @param JobPositionRequest $request
     * @return JobPositionResource
     */
    public function create(JobPositionRequest $request): JobPositionResource
    {
        return new JobPositionResource($this->jobPositionService->createJobPosition($request->all()));
    }

    /**
     * @param JobPosition $jobPosition
     * @param JobPositionRequest $request
     * @return JsonResponse
     */
    public function update(JobPosition $jobPosition, JobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->updateJobPosition($jobPosition, $request->all());
        return response()->json([], 204);
    }

    /**
     * @param JobPosition $jobPosition
     * @return JsonResponse
     */
    public function delete(JobPosition $jobPosition): JsonResponse
    {
        $this->jobPositionService->deleteJobPosition($jobPosition);
        return response()->json([], 204);
    }
}
