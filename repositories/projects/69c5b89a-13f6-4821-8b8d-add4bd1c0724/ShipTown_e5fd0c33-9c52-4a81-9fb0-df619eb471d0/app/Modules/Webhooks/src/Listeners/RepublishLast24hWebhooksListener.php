<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Modules\Webhooks\src\Jobs\RepublishLast24hWebhooksJob;

class RepublishLast24hWebhooksListener
{
    public function handle()
    {
        RepublishLast24hWebhooksJob::dispatch();
    }
}
