<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Modules\Webhooks\src\Jobs\ClearOldWebhookRecordsJob;

class ClearOldWebhooksListener
{
    public function handle()
    {
        ClearOldWebhookRecordsJob::dispatch();
    }
}
