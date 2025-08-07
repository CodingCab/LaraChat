<?php

namespace App\Modules\CsvProductImports\src\Listeners;

use App\Events\Warehouse\WarehouseCreatedEvent;
use App\Modules\CsvProductImports\src\Services\CsvProductImportTableService;

class WarehouseCreatedEventListener
{
    public function handle(WarehouseCreatedEvent $event): void
    {
        CsvProductImportTableService::dispatchAddColumnsJob($event->warehouse);
    }
}
