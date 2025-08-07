<?php

namespace App\Modules\CsvProductImports\src\Listeners;

use App\Events\Warehouse\WarehouseUpdatedEvent;
use App\Modules\CsvProductImports\src\Services\CsvProductImportTableService;

class WarehouseUpdatedEventListener
{
    public function handle(WarehouseUpdatedEvent $event): void
    {
        if ($event->oldCode) {
            CsvProductImportTableService::dispatchUpdateColumnsJob($event->warehouse, $event->oldCode);
        }
    }
}
