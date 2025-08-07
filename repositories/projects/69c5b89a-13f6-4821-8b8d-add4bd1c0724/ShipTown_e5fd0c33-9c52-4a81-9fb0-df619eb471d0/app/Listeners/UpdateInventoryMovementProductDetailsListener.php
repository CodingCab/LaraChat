<?php

namespace App\Listeners;

use App\Jobs\UpdateInventoryMovementProductDetailsJob;

class UpdateInventoryMovementProductDetailsListener
{
    public function handle(): void
    {
        UpdateInventoryMovementProductDetailsJob::dispatch();
    }
}
