<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Resources\JobPositionResource;
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
        return $this->jobPositionService->getAllJobPositions();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function indexList(): LengthAwarePaginator
    {
        return $this->jobPositionService->getAllJobPositionsList();
    }

    /**
     * @param JobPositionStoreRequest $request
     * @return JobPosition
     */
    public function create(JobPositionStoreRequest $request): JobPositionResource
    {
        return new JobPositionResource($this->jobPositionService->createJobPosition($request->all()));
    }

    /**
     * @param JobPosition $jobPosition
     * @param JobPositionRequest $request
     * @return JsonResponse
     */
    public function update(JobPosition $jobPosition, JobPositionUpdateRequest $request): JsonResponse
    {
        $this->jobPositionService->updateJobPosition($jobPosition, $request->all());
        return response(204)->json();
    }

    /**
     * @param JobPosition $jobPosition
     * @return JsonResponse
     */
    public function delete(JobPosition $jobPosition): JsonResponse
    {
        $this->jobPositionService->delete($jobPosition);
        return response(204)->json();
    }
}
