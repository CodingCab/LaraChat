<?php

namespace App\Http\Controllers\Api\Modules\ScheduledReport;

use App\Http\Controllers\Controller;
use App\Modules\ScheduledReport\src\Http\Requests\StoreRequest;
use App\Modules\ScheduledReport\src\Http\Requests\UpdateRequest;
use App\Modules\ScheduledReport\src\Http\Resources\ScheduledReportResource;
use App\Modules\ScheduledReport\src\Models\ScheduledReport;

class ScheduledReportController extends Controller
{

    public function index()
    {
        $scheduledReports = ScheduledReport::getSpatieQueryBuilder();
        return ScheduledReportResource::collection($this->getPaginatedResult($scheduledReports));
    }

    public function store(StoreRequest $request)
    {
        $scheduledReport = new ScheduledReport;
        $scheduledReport->fill($request->validated());

        $cron = new \Cron\CronExpression($scheduledReport->cron);
        $nextCron = $cron->getNextRunDate();

        $scheduledReport->next_run_at = $nextCron;
        $scheduledReport->save();

        return ScheduledReportResource::make($scheduledReport);
    }

    public function update(UpdateRequest $request, ScheduledReport $scheduledReport)
    {
        $scheduledReport->fill($request->validated());

        $cron = new \Cron\CronExpression($scheduledReport->cron);
        $nextCron = $cron->getNextRunDate();

        $scheduledReport->next_run_at = $nextCron;
        $scheduledReport->save();

        return ScheduledReportResource::make($scheduledReport);
    }

    public function destroy(ScheduledReport $scheduledReport)
    {
        $scheduledReport->delete();
        return ScheduledReportResource::make($scheduledReport);
    }
}
