<?php

namespace App\Modules\Magento2API\PriceSync\src;

use App\Events\EveryDayEvent;
use App\Events\EveryHourEvent;
use App\Events\EveryMinuteEvent;
use App\Events\EveryTenMinutesEvent;
use App\Events\Product\ProductPriceUpdatedEvent;
use App\Events\Product\ProductTagAttachedEvent;
use App\Events\Product\ProductTagDetachedEvent;
use App\Models\ManualRequestJob;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class PriceSyncServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Magento 2 API - Price Sync';

    public static string $module_description = 'Module provides ability to sync prices with Magento 2 API';

    public static string $settings_link = '/settings/modules/magento2api/price-sync';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EveryMinuteEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\EveryMinuteEventListener::class,
        ],

        EveryTenMinutesEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\EveryTenMinutesEventListener::class,
        ],

        EveryHourEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\EveryHourEventListener::class,
        ],

        EveryDayEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\EveryDayEventListener::class,
        ],

        ProductTagAttachedEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\ProductTagAttachedEventListener::class,
        ],

        ProductTagDetachedEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\ProductTagDetachedEventListener::class,
        ],

        ProductPriceUpdatedEvent::class => [
            \App\Modules\Magento2API\PriceSync\src\Listeners\ProductPriceUpdatedEventListener::class,
        ],
    ];

    public static function enabling(): bool
    {
        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - CheckIfSyncIsRequiredJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\CheckIfSyncIsRequiredJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - EnsureProductPriceIdIsFilledJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductPriceIdIsFilledJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - EnsureProductRecordsExistJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductRecordsExistJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - FetchBasePricesJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\FetchBasePricesJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - FetchSpecialPricesJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\FetchSpecialPricesJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - SyncProductBasePricesJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductBasePricesJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - SyncProductBasePricesBulkJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductBasePricesBulkJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - SyncProductSalePricesBulkJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductSalePricesBulkJob::class,
        ], ['job_class'], ['job_name']);

        ManualRequestJob::query()->upsert([
            'job_name' => 'Magento 2.0 API Price Sync - SyncProductSalePricesJob',
            'job_class' => \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductSalePricesJob::class,
        ], ['job_class'], ['job_name']);

        return parent::enabling();
    }

    public static function disabling(): bool
    {
        ManualRequestJob::query()->whereIn('job_class', [
            \App\Modules\Magento2API\PriceSync\src\Jobs\CheckIfSyncIsRequiredJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductPriceIdIsFilledJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\EnsureProductRecordsExistJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\FetchBasePricesJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\FetchSpecialPricesJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductBasePricesJob::class,
            \App\Modules\Magento2API\PriceSync\src\Jobs\SyncProductSalePricesJob::class,
        ])->delete();

        return parent::disabling();
    }
}
