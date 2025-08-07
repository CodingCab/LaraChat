<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivityIndexRequest;
use App\Http\Requests\Api\ActivityStoreRequest;
use App\Http\Resources\LogResource;
use App\Modules\Reports\src\Models\ActivityReport;
use App\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(ActivityIndexRequest $request): AnonymousResourceCollection
    {
        $report = new ActivityReport;

        return LogResource::collection($report->queryBuilder()->simplePaginate());
    }

    public function store(ActivityStoreRequest $request): JsonResource
    {
        $activityID = Activity::query()->insertGetId([
            'log_name' => data_get($request->validated(), 'log_name'),
            'description' => data_get($request->validated(), 'description'),
            'properties' => data_get($request->validated(), 'properties'),
            'subject_type' => data_get($request->validated(), 'subject_type'),
            'subject_id' => data_get($request->validated(), 'subject_id'),
            'causer_type' => $request->user() ? User::class : null,
            'causer_id' => data_get($request->user(), 'id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        abort_unless($activityID, 500, 'activity not saved, try again');

        return JsonResource::make(['id' => $activityID]);
    }
}
