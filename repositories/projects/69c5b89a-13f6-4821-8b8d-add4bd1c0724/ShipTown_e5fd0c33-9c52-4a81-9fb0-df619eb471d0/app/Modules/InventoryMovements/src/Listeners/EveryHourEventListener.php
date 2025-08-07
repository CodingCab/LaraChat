<?php

namespace App\Modules\InventoryMovements\src\Listeners;

use App\Modules\InventoryMovements\src\Jobs\CheckForIncorrectSequenceNumberJob;
use App\Modules\InventoryMovements\src\Jobs\InventoryQuantityCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityAfterCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityBeforeCheckJob;
use App\Modules\InventoryMovements\src\Jobs\QuantityDeltaCheckJob;

class EveryHourEventListener
{
    public function handle()
    {
        CheckForIncorrectSequenceNumberJob::dispatch(now()->subDay());
        QuantityAfterCheckJob::dispatch(now()->subDay());
        QuantityBeforeCheckJob::dispatch(now()->subDay());
        QuantityDeltaCheckJob::dispatch(now()->subDay());
        InventoryQuantityCheckJob::dispatch();
    }
}
