<?php

namespace App\Observers;

use App\Events\PrintJob\PrintJobCreatedEvent;

class PrintJobObserver
{
    public function created($printJob): void
    {
        PrintJobCreatedEvent::dispatch($printJob);
    }
}
