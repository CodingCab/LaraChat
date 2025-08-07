<?php

namespace App\Observers;

use App\Models\Warehouse;
use App\Events\Warehouse\WarehouseCreatedEvent;
use App\Events\Warehouse\WarehouseDeletedEvent;
use App\Events\Warehouse\WarehouseUpdatedEvent;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllInventoryRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\Products\EnsureAllProductPriceRecordsExistsJob;
use App\Modules\Maintenance\src\Jobs\UpdateInventoryWarehouseCodeJob;
use App\Modules\Maintenance\src\Jobs\UpdateProductPriceWarehouseCodeJob;

class WarehouseObserver
{
    public function created(Warehouse $warehouse): void
    {
        EnsureAllInventoryRecordsExistsJob::dispatch();
        EnsureAllProductPriceRecordsExistsJob::dispatch();
        WarehouseCreatedEvent::dispatch($warehouse);
    }

    public function updated(Warehouse $warehouse): void
    {
        UpdateInventoryWarehouseCodeJob::dispatch($warehouse);
        UpdateProductPriceWarehouseCodeJob::dispatch($warehouse);

        if ($warehouse->wasChanged('code')) {
            WarehouseUpdatedEvent::dispatch($warehouse, $warehouse->getOriginal('code'));
            return;
        }

        WarehouseUpdatedEvent::dispatch($warehouse);
    }

    public function deleted(Warehouse $warehouse): void
    {
        WarehouseDeletedEvent::dispatch($warehouse);
    }
}
