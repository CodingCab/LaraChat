<?php

namespace App\Modules\Webhooks\src\Listeners;

use App\Events\OrderProduct\OrderProductShipmentCreatedEvent;
use App\Models\OrderProductShipment;
use App\Modules\Webhooks\src\Models\PendingWebhook;

class OrderProductShipmentCreatedListener
{
    public function handle(OrderProductShipmentCreatedEvent $event)
    {
        PendingWebhook::query()->firstOrCreate([
            'model_class' => OrderProductShipment::class,
            'model_id' => $event->orderProductShipment->getKey(),
            'reserved_at' => null,
            'published_at' => null,
        ]);
    }
}
