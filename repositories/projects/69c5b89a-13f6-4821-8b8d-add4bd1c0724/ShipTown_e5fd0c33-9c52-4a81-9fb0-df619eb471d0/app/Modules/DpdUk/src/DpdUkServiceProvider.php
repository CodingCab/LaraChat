<?php

namespace App\Modules\DpdUk\src;

use App\Models\ShippingService;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class InventoryQuantityReservedServiceProvider.
 */
class DpdUkServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - DPD UK Integration';

    public static string $module_description = 'Provides seamless integration with DPD UK';

    public static string $settings_link = '/settings/modules/dpd-uk';

    public static bool $autoEnable = false;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    public static function enabling(): bool
    {
        ShippingService::query()
            ->updateOrCreate([
                'code' => 'dpd_uk_next_day',
            ], [
                'service_provider_class' => Services\NextDayShippingService::class,
            ]);

        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()
            ->where(['code' => 'dpd_uk_next_day'])
            ->delete();

        return true;
    }
}
