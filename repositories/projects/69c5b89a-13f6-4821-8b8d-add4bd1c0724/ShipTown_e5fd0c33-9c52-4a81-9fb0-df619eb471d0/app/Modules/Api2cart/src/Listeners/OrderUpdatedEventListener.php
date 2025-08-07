<?php

namespace App\Modules\Api2cart\src\Listeners;

use App\Events\Order\OrderUpdatedEvent;
use App\Modules\Api2cart\src\Jobs\SyncOrderStatus;

class OrderUpdatedEventListener
{
    public function handle(OrderUpdatedEvent $event): void
    {
        if ($event->order->isAttributeNotChanged('status_code')) {
            return;
        }

        if ($event->order->orderStatus->sync_ecommerce) {
            SyncOrderStatus::dispatch($event->order);
        }
    }
}
