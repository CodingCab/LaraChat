<?php

namespace App\Modules\Telescope\src\Listeners;

use App\Modules\Telescope\src\Jobs\PruneEntriesJob;

class HourlyEventListener
{
    public function handle(): bool
    {
        PruneEntriesJob::dispatch();

        return true;
    }
}
