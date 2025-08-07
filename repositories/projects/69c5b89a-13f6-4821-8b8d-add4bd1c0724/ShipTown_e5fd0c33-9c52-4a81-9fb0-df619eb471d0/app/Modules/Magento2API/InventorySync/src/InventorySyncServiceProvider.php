<?php

namespace App\Modules\Magento2API\InventorySync\src;

use App\Events\EveryDayEvent;
use App\Events\EveryFiveMinutesEvent;
use App\Events\EveryHourEvent;
use App\Events\Inventory\RecalculateInventoryRequestEvent;
use App\Events\InventoryTotalsByWarehouseTagUpdatedEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Models\ManualRequestJob;
use App\Modules\BaseModuleServiceProvider;

class InventorySyncServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Magento 2 API - Inventory Sync';

    public static string $module_description = 'Module provides ability to sync inventory with Magento 2 API (MSI)';

    public static string $settings_link = '/settings/modules/magento2api/inventory-sync';

    public static bool $autoEnable = false;

    protected $listen = [
        EveryFiveMinutesEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\EveryFiveMinutesEventListener::class,
        ],

        EveryHourEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\EveryHourEventListener::class,
        ],

        EveryDayEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\EveryDayEventListener::class,
        ],

        ProductTagAttachedEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\ProductTagAttachedEventListener::class,
        ],

        ProductTagDetachedEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\ProductTagDetachedEventListener::class,
        ],

        InventoryTotalsByWarehouseTagUpdatedEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\InventoryTotalsByWarehouseTagUpdatedEventListener::class,
        ],

        RecalculateInventoryRequestEvent::class => [
            \App\Modules\Magento2API\InventorySync\src\Listeners\RecalculateInventoryRequestEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - AssignInventorySourceJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\AssignInventorySourceJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - CheckIfSyncIsRequiredJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\CheckIfSyncIsRequiredJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - EnsureProductRecordsExistJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\EnsureProductRecordsExistJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - FetchStockItemsJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\FetchStockItemsJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - GetProductIdsJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\GetProductIdsJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - SyncProductInventoryJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\SyncProductInventoryJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - EnsureInventoryGroupIdIsNotNullJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\EnsureProductRecordsExistJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - EnsureInventoryGroupIdIsNotNullJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\RecheckIfProductsExistInMagentoJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2 MSI - RemoveProductsWithoutRequiredTagJob',
            'job_class' => \App\Modules\Magento2API\InventorySync\src\Jobs\RemoveProductsWithoutRequiredTagJob::class,
        ], ['job_class'], ['job_name']);

        return parent::enabling();
    }
}
