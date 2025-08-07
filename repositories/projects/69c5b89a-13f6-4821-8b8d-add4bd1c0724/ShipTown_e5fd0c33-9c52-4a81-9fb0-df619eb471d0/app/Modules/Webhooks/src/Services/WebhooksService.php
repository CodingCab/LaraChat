<?php

namespace App\Modules\Webhooks\src\Services;

use App\Modules\Webhooks\src\Jobs\PublishInventoryMovementWebhooksJob;
use App\Modules\Webhooks\src\Jobs\PublishInventoryWebhooksJob;
use App\Modules\Webhooks\src\Jobs\PublishOrderProductShipmentWebhooksJob;

class WebhooksService
{
    public static function dispatchJobs()
    {
        PublishInventoryMovementWebhooksJob::dispatch();
        PublishOrderProductShipmentWebhooksJob::dispatch();
        PublishInventoryWebhooksJob::dispatch();
    }
}
