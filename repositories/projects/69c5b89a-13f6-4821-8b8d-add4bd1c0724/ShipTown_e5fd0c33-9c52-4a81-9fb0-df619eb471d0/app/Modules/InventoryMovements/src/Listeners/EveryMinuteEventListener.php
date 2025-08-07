<?php

namespace App\Modules\InventoryMovements\src\Listeners;

use App\Modules\InventoryMovements\src\Jobs\SequenceNumberJob;

class EveryMinuteEventListener
{
    public function handle()
    {
        SequenceNumberJob::dispatch();
    }
}
