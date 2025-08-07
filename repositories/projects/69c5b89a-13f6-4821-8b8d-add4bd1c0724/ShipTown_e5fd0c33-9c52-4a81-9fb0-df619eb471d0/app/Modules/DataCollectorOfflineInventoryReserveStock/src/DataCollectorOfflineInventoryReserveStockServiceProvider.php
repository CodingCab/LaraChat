<?php

namespace App\Modules\DataCollectorOfflineInventoryReserveStock\src;

use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Events\EveryDayEvent;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\CreateInventoryReservationsForExistingOfflineInventoryJob;
use App\Modules\DataCollectorOfflineInventoryReserveStock\src\Jobs\DeleteExistingInventoryReservationsJob;

class DataCollectorOfflineInventoryReserveStockServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Offline Inventory Reserve Stock';

    public static string $module_description = 'Module provides an ability to reserve stock for offline inventory';

    public static bool $autoEnable = true;

    protected $listen = [
        DataCollectionDeletedEvent::class => [
            Listeners\DataCollectionDeletedEventListener::class,
        ],
        DataCollectionRecordCreatedEvent::class => [
            Listeners\DataCollectionRecordCreatedEventListener::class,
        ],
        DataCollectionRecordUpdatedEvent::class => [
            Listeners\DataCollectionRecordUpdatedEventListener::class,
        ],
        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        CreateInventoryReservationsForExistingOfflineInventoryJob::dispatch();

        return true;
    }

    public static function disabling(): bool
    {
        DeleteExistingInventoryReservationsJob::dispatch();

        return true;
    }
}
