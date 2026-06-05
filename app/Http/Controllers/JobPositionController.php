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
     * @param JobPositionServiceInterface $jobPositionService
     */
    public function __construct(
        private readonly JobPositionServiceInterface $jobPositionService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/job-position",
     *     tags={"JobPosition"},
     *     summary="Lista stanowisk (paginacja)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(allOf={
     *
     *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *             @OA\Schema(@OA\Property(property="data", type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/JobPositionResource")
     *             ))
     *         })
     *     )
     * )
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositions();
    }

    /**
     * @OA\Get(
     *     path="/api/job-position/selectlist",
     *     tags={"JobPosition"},
     *     summary="Lista stanowisk do selecta (uuid + name)",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(response=200, description="OK")
     * )
     *
     * @return LengthAwarePaginator
     */
    public function selectList(): LengthAwarePaginator
    {
        return $this->jobPositionService->getJobPositionsList();
    }

    /**
     * @OA\Get(
     *     path="/api/job-position/{uuid}",
     *     tags={"JobPosition"},
     *     summary="Pobiera jedno stanowisko",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="OK",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/JobPositionResource"))
     *     ),
     *
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param JobPosition $jobPosition
     * @return JobPositionResource
     */
    public function show(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }

    /**
     * @OA\Post(
     *     path="/api/job-position",
     *     tags={"JobPosition"},
     *     summary="Tworzy nowe stanowisko",
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","f_name","m_name"},
     *
     *             @OA\Property(property="name", type="string", example="Stomatolog"),
     *             @OA\Property(property="f_name", type="string", example="Stomatolożka"),
     *             @OA\Property(property="m_name", type="string", example="Stomatolodzy")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Utworzono",
     *
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/JobPositionResource"))
     *     ),
     *
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param JobPositionRequest $request
     * @return JobPositionResource
     */
    public function store(JobPositionRequest $request): JobPositionResource
    {
        return new JobPositionResource($this->jobPositionService->createJobPosition($request->all()));
    }

    /**
     * @OA\Put(
     *     path="/api/job-position/{uuid}",
     *     tags={"JobPosition"},
     *     summary="Aktualizuje stanowisko",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","f_name","m_name"},
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="f_name", type="string"),
     *             @OA\Property(property="m_name", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=204, description="Zaktualizowano"),
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param JobPosition $jobPosition
     * @param JobPositionRequest $request
     * @return JsonResponse
     */
    public function update(JobPosition $jobPosition, JobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->updateJobPosition($jobPosition, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Delete(
     *     path="/api/job-position/{uuid}",
     *     tags={"JobPosition"},
     *     summary="Usuwa stanowisko",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=204, description="Usunięto"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param JobPosition $jobPosition
     * @return JsonResponse
     */
    public function destroy(JobPosition $jobPosition): JsonResponse
    {
        $this->jobPositionService->deleteJobPosition($jobPosition);

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Patch(
     *     path="/api/user/{uuid}/jobposition",
     *     tags={"JobPosition"},
     *     summary="Przypisuje stanowiska do użytkownika",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"job_positions"},
     *
     *             @OA\Property(property="job_positions", type="array",
     *
     *                 @OA\Items(type="string", format="uuid")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=204, description="Przypisano"),
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param User $user
     * @param AssignJobPositionRequest $request
     * @return JsonResponse
     */
    public function assignJobPosition(User $user, AssignJobPositionRequest $request): JsonResponse
    {
        $this->jobPositionService->assignJobPosition($user, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/job-position/export",
     *     tags={"JobPosition"},
     *     summary="Eksport stanowisk do pliku",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(name="type", in="query", required=true,
     *
     *         @OA\Schema(type="string", enum={"xlsx","csv","ods"})
     *     ),
     *
     *     @OA\Response(response=200, description="Plik do pobrania",
     *
     *         @OA\MediaType(mediaType="application/octet-stream")
     *     )
     * )
     *
     * @param ExportRequest $request
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
