<?php

namespace App\Observers;

use App\Events\OrderProductPick\OrderProductPickCreatedEvent;
use App\Events\OrderProductPick\OrderProductPickDeletedEvent;
use App\Models\OrderProductPick;

class OrderProductPickObserver
{
    public function created(OrderProductPick $orderProductPick): void
    {
        OrderProductPickCreatedEvent::dispatch($orderProductPick);
    }

    public function deleted(OrderProductPick $orderProductPick): void
    {
        OrderProductPickDeletedEvent::dispatch($orderProductPick);
    }
}
