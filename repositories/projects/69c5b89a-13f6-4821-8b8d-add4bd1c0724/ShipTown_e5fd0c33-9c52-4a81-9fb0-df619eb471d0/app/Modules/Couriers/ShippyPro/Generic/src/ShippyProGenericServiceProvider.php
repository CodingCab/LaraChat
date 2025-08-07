<?php

namespace App\Modules\Couriers\ShippyPro\Generic\src;

use App\Models\ShippingService;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\Couriers\ShippyPro\ShippyProApi;

class ShippyProGenericServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - S Pro Integration';

    public static string $module_description = 'Provides generic with SPro integration';

    public static string $settings_link = '/settings/modules/couriers/generic';

    public static bool $autoEnable = false;

    protected $listen = [];

    public static function enabling(): bool
    {
        if (ShippyProApi::checkApiConnection() === false) {
            return false;
        }

        ShippingService::query()
            ->updateOrCreate([
                'code' => 's_pro_generic',
            ], [
                'service_provider_class' => Services\GenericService::class,
                'connection_details' => [
                    'carrier_id' => env('SHIPPY_PRO_GENERIC_CARRIER_ID', ''),
                ],
            ]);

        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()
            ->where(['code' => 's_pro_generic'])
            ->delete();

        return true;
    }
}
