<?php

namespace App\Http\Controllers\Api\Modules\OrderAutomations;

use App\Http\Controllers\Controller;
use App\Modules\Automations\src\Http\Requests\RunAutomationRequest;
use App\Modules\Automations\src\Jobs\RunAutomationJob;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RunAutomationController extends Controller
{
    public function store(RunAutomationRequest $request): JsonResource
    {
        RunAutomationJob::dispatch($request->validated()['automation_id']);

        return JsonResource::make([
            'job_requested' => true,
            'automation_id' => $request->validated()['automation_id'],
            'time' => Carbon::now(),
        ]);
    }
}
