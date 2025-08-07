<?php

namespace App\Modules\Rmsapi\src\Listeners;

use App\Modules\Rmsapi\src\Jobs\RepublishWebhooksForSyncRequired;

class EveryHourEventListener
{
    public function handle(): void
    {
        RepublishWebhooksForSyncRequired::dispatch();
    }
}
