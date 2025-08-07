<?php

namespace App\Modules\CsvProductImports\src;

use App\Events\EveryMinuteEvent;
use App\Events\Warehouse\WarehouseCreatedEvent;
use App\Events\Warehouse\WarehouseDeletedEvent;
use App\Events\Warehouse\WarehouseUpdatedEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\CsvProductImports\src\Jobs\AddWarehouseColumnsToCsvProductImportTableJob;

class CsvProductImportsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'CSV Product Imports';

    public static string $module_description = 'Store and process CSV product imports.';

    public static bool $autoEnable = true;

    protected $listen = [
        EveryMinuteEvent::class => [
            Listeners\EveryMinuteEventListener::class,
        ],
        WarehouseCreatedEvent::class => [
            Listeners\WarehouseCreatedEventListener::class,
        ],
        WarehouseUpdatedEvent::class => [
            Listeners\WarehouseUpdatedEventListener::class,
        ],
        WarehouseDeletedEvent::class => [
            Listeners\WarehouseDeletedEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        AddWarehouseColumnsToCsvProductImportTableJob::dispatch();

        return parent::enabling();
    }
}
