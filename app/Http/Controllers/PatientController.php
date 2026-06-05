<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportRequest;
use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of PatientController
 */
class PatientController extends Controller
{
    /**
     * @param PatientServiceInterface $patientService
     */
    public function __construct(
        private readonly PatientServiceInterface $patientService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/patient",
     *     tags={"Patient"},
     *     summary="Lista pacjentów (paginacja)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK",
     *         @OA\JsonContent(allOf={
     *             @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *             @OA\Schema(@OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/PatientResource")
     *             ))
     *         })
     *     )
     * )
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return $this->patientService->getPatients();
    }

    /**
     * @OA\Get(
     *     path="/api/patient/selectlist",
     *     tags={"Patient"},
     *     summary="Lista pacjentów do selecta (uuid + name)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK")
     * )
     *
     * @return LengthAwarePaginator
     */
    public function selectList(): LengthAwarePaginator
    {
        return $this->patientService->getPatientsList();
    }

    /**
     * @OA\Get(
     *     path="/api/patient/{uuid}",
     *     tags={"Patient"},
     *     summary="Pobiera jednego pacjenta",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/PatientResource"))
     *     ),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param Patient $patient
     * @return PatientResource
     */
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource($patient);
    }

    /**
     * @OA\Post(
     *     path="/api/patient",
     *     tags={"Patient"},
     *     summary="Tworzy nowego pacjenta",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","pesel"},
     *             @OA\Property(property="first_name", type="string", example="Anna"),
     *             @OA\Property(property="last_name", type="string", example="Nowak"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="pesel", type="string", example="98010112345")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Utworzono",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/PatientResource"))
     *     ),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param PatientStoreRequest $request
     * @return PatientResource
     */
    public function store(PatientStoreRequest $request): PatientResource
    {
        return new PatientResource($this->patientService->createPatient($request->all()));
    }

    /**
     * @OA\Put(
     *     path="/api/patient/{uuid}",
     *     tags={"Patient"},
     *     summary="Aktualizuje pacjenta",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","pesel"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="pesel", type="string")
     *         )
     *     ),
     *     @OA\Response(response=204, description="Zaktualizowano"),
     *     @OA\Response(response=404, description="Nie znaleziono"),
     *     @OA\Response(response=422, description="Błąd walidacji")
     * )
     *
     * @param Patient $patient
     * @param PatientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(Patient $patient, PatientUpdateRequest $request): JsonResponse
    {
        $this->patientService->updatePatient($patient, $request->all());

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Delete(
     *     path="/api/patient/{uuid}",
     *     tags={"Patient"},
     *     summary="Usuwa pacjenta",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="Usunięto"),
     *     @OA\Response(response=404, description="Nie znaleziono")
     * )
     *
     * @param Patient $patient
     * @return JsonResponse
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $this->patientService->deletePatient($patient);

        return new JsonResponse(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/patient/export",
     *     tags={"Patient"},
     *     summary="Eksport pacjentów do pliku",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="type", in="query", required=true,
     *         @OA\Schema(type="string", enum={"xlsx","csv","ods"})
     *     ),
     *     @OA\Response(response=200, description="Plik do pobrania",
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
        return $this->patientService->export($request);
    }
}
