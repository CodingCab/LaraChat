<?php

namespace App\Modules\DataCollectorTransfersOutReserveStock\src;

use App\Events\DataCollection\DataCollectionDeletedEvent;
use App\Events\DataCollection\DataCollectionUpdatedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordCreatedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordDeletedEvent;
use App\Events\DataCollectionRecord\DataCollectionRecordUpdatedEvent;
use App\Events\EveryDayEvent;
use App\Models\ManualRequestJob;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CheckReservationsDataIntegrityJob;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\CreateInventoryReservationsForExistingTransfersOutJob;
use App\Modules\DataCollectorTransfersOutReserveStock\src\Jobs\DeleteExistingInventoryReservationsJob;

class DataCollectorTransfersOutReserveStockServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Transfers Out Reserve Stock';

    public static string $module_description = 'Module provides an ability to reserve stock for transfers out';

    public static bool $autoEnable = true;

    protected $listen = [
        DataCollectionDeletedEvent::class => [
            Listeners\DataCollectionDeletedEventListener::class,
        ],
        DataCollectionUpdatedEvent::class => [
            Listeners\DataCollectionUpdatedEventListener::class,
        ],
        DataCollectionRecordCreatedEvent::class => [
            Listeners\DataCollectionRecordCreatedEventListener::class,
        ],
        DataCollectionRecordUpdatedEvent::class => [
            Listeners\DataCollectionRecordUpdatedEventListener::class,
        ],
        DataCollectionRecordDeletedEvent::class => [
            Listeners\DataCollectionRecordDeletedEventListener::class,
        ],
        EveryDayEvent::class => [
            Listeners\EveryDayEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        CreateInventoryReservationsForExistingTransfersOutJob::dispatch();

        ManualRequestJob::query()->create([
            'job_name' => 'Check Reservations Table Integrity',
            'job_class' => CheckReservationsDataIntegrityJob::class,
        ]);

        return true;
    }

    public static function disabling(): bool
    {
        DeleteExistingInventoryReservationsJob::dispatch();

        ManualRequestJob::query()
            ->where('job_class', CheckReservationsDataIntegrityJob::class)
            ->delete();

        return true;
    }
}
