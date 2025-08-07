<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobIndexRequest;
use App\Http\Requests\JobStoreRequest;
use App\Http\Resources\ManualRequestJobResource;
use App\Models\ManualRequestJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobsController extends Controller
{
    public function index(JobIndexRequest $request): AnonymousResourceCollection
    {
        return ManualRequestJobResource::collection(ManualRequestJob::query()->get());
    }

    public function store(JobStoreRequest $request): JsonResponse
    {
        $job = ManualRequestJob::findOrFail($request->get('job_id'));
        dispatch(new $job->job_class);

        return response()->json(['message' => 'Job dispatched', 'job_class' => $job->job_class]);
    }
}
