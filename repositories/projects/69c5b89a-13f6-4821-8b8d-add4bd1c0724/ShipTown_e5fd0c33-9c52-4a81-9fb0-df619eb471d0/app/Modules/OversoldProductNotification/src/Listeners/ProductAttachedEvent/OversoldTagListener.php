<?php

namespace App\Modules\OversoldProductNotification\src\Listeners\ProductAttachedEvent;

use App\Events\Product\ProductTagAttachedEvent;
use App\Modules\OversoldProductNotification\src\Jobs\SendEmailNotification;

class OversoldTagListener
{
    public function handle(ProductTagAttachedEvent $event)
    {
        if ($event->tag() === 'oversold') {
            SendEmailNotification::dispatch($event->product()->id);
        }
    }
}
