<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobPositionRequest;
use App\Http\Resources\JobPositionResource;
use App\Models\JobPosition;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JobPositionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', JobPosition::class);

        return JobPositionResource::collection(JobPosition::all());
    }

    public function store(JobPositionRequest $request)
    {
        $this->authorize('create', JobPosition::class);

        return new JobPositionResource(JobPosition::create($request->validated()));
    }

    public function show(JobPosition $jobPosition)
    {
        $this->authorize('view', $jobPosition);

        return new JobPositionResource($jobPosition);
    }

    public function update(JobPositionRequest $request, JobPosition $jobPosition)
    {
        $this->authorize('update', $jobPosition);

        $jobPosition->update($request->validated());

        return new JobPositionResource($jobPosition);
    }

    public function destroy(JobPosition $jobPosition)
    {
        $this->authorize('delete', $jobPosition);

        $jobPosition->delete();

        return response()->json();
    }
}
