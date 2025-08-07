<?php

namespace App\Modules\Automations\src\Listeners;

use App\Events\Order\OrderUpdatedEvent;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;

class OrderUpdatedListener
{
    public function handle(OrderUpdatedEvent $event): void
    {
        RunEnabledAutomationsOnSpecificOrderJob::dispatchSync($event->order->getKey());
    }
}
