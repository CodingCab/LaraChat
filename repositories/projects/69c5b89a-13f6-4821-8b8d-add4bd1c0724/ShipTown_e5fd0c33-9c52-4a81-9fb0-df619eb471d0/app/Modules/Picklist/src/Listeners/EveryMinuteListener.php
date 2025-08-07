<?php

namespace App\Modules\Picklist\src\Listeners;

use App\Modules\Picklist\src\Jobs\DistributePicksJob;
use App\Modules\Picklist\src\Jobs\UnDistributeDeletedPicksJob;

class EveryMinuteListener
{
    public function handle(): void
    {
        DistributePicksJob::dispatch();
        UnDistributeDeletedPicksJob::dispatch();
    }
}
