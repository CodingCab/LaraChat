<?php

namespace App\Modules\DataCollector\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\DataCollection;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class DispatchCollectionsTasksJob extends UniqueJob
{
    public function handle(): void
    {
        DataCollection::withTrashed()
            ->whereNotNull('currently_running_task')
            ->chunkById(1, function ($batch) {
                $batch->each(function (DataCollection $dataCollection) {
                    try {
                        /** @var Dispatchable $job */
                        $job = $dataCollection->currently_running_task;
                        $job::dispatch($dataCollection->getKey());
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                        report($e);
                    }
                });
            });
    }
}
