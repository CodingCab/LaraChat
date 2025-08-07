<?php

namespace App\Modules\PrintNode\src\Listeners;

use App\Events\PrintJob\PrintJobCreatedEvent;
use App\Exceptions\ShippingServiceException;
use App\Modules\PrintNode\src\PrintNode;

class PrintJobListener
{
    /**
     * @throws ShippingServiceException
     */
    public function handle(PrintJobCreatedEvent $event): void
    {
        PrintNode::print($event->printJob);
    }
}
