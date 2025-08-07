<?php

namespace App\Modules\Automations\src\Listeners;

use App\Events\Order\OrderCreatedEvent;
use App\Modules\Automations\src\Jobs\RunEnabledAutomationsOnSpecificOrderJob;

class OrderCreatedListener
{
    public function handle(OrderCreatedEvent $event): void
    {
        RunEnabledAutomationsOnSpecificOrderJob::dispatchSync($event->order->getKey());
    }
}
