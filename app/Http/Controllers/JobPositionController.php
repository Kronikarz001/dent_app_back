<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignJobPositionRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\JobPositionRequest;
use App\Http\Resources\JobPositionResource;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\JobPositionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of JobPositionController
 */
class JobPositionController extends Controller
{
    /**
     * @param  JobPositionServiceInterface  $jobPositionService
     */
    public function __construct(
        private readonly JobPositionServiceInterface $jobPositionService
    ) {}

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
    public function selectList(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositionsList();
    }

    /**
     * @param  JobPosition  $jobPosition
     * @return JobPositionResource
     */
    public function show(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }

    /**
     * @param  JobPositionRequest  $request
     * @return JobPositionResource
     */
    public function store(JobPositionRequest $request): JobPositionResource
    {
        return new JobPositionResource($this->jobPositionService->createJobPosition($request->all()));
    }

    /**
     * @param  JobPosition  $jobPosition
     * @param  JobPositionRequest  $request
     * @return JsonResponse
     */
    public function update(JobPosition $jobPosition, JobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->updateJobPosition($jobPosition, $request->all());

        return response()->json([], 204);
    }

    /**
     * @param  JobPosition  $jobPosition
     * @return JsonResponse
     */
    public function destroy(JobPosition $jobPosition): JsonResponse
    {
        $this->jobPositionService->deleteJobPosition($jobPosition);

        return response()->json([], 204);
    }

    /**
     * @param  User  $user
     * @param  AssignJobPositionRequest  $request
     * @return JsonResponse
     */
    public function assignJobPosition(User $user, AssignJobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->assignJobPosition($user, $request->all());

        return response()->json([], 204);
    }

    /**
     * @param  ExportRequest  $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->jobPositionService->export($request);
    }
}
