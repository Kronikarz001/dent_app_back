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
    ) {
    }

    /**
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return $this->patientService->getPatients();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function selectList(): LengthAwarePaginator
    {
        return $this->patientService->getPatientsList();
    }

    /**
     * @param Patient $patient
     * @return PatientResource
     */
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource($patient);
    }

    /**
     * @param PatientStoreRequest $request
     * @return PatientResource
     */
    public function store(PatientStoreRequest $request): PatientResource
    {
        return new PatientResource($this->patientService->createPatient($request->all()));
    }

    /**
     * @param Patient $patient
     * @param PatientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(Patient $patient, PatientUpdateRequest $request): JsonResponse
    {
        $this->patientService->updatePatient($patient, $request->all());
        return response()->json([], 204);
    }

    /**
     * @param Patient $patient
     * @return JsonResponse
     */
    public function destroy(Patient $patient): JsonResponse
    {
        $this->patientService->deletePatient($patient);
        return response()->json([], 204);
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->patientService->export($request);
    }
}
