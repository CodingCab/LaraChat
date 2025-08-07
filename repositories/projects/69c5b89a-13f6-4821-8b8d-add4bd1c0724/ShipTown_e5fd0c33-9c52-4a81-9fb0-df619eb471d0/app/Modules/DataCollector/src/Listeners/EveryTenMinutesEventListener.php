<?php

namespace App\Modules\DataCollector\src\Listeners;

use App\Modules\DataCollector\src\Jobs\DispatchCollectionsTasksJob;

class EveryTenMinutesEventListener
{
    public function handle(): void
    {
        DispatchCollectionsTasksJob::dispatch();
    }
}
