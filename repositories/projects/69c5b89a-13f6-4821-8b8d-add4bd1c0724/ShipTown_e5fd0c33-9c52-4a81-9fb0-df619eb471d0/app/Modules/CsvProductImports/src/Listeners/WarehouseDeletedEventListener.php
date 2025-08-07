<?php

namespace App\Modules\CsvProductImports\src\Listeners;

use App\Events\Warehouse\WarehouseDeletedEvent;
use App\Modules\CsvProductImports\src\Services\CsvProductImportTableService;

class WarehouseDeletedEventListener
{
    public function handle(WarehouseDeletedEvent $event): void
    {
        CsvProductImportTableService::dispatchRemoveColumnsJob($event->warehouse);
    }
}
