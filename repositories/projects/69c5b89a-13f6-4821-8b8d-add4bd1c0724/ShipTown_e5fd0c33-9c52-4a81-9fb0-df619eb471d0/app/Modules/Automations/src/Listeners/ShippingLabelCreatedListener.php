<?php

namespace App\Modules\Automations\src\Listeners;

use App\Events\ShippingLabel\ShippingLabelCreatedEvent;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;

class ShippingLabelCreatedListener
{
    public function handle(ShippingLabelCreatedEvent $event): void
    {
        RunEnabledAutomationsOnSpecificOrderJob::dispatchSync($event->shippingLabel->order_id);
    }
}
