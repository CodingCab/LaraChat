<?php

namespace App\Modules\Couriers\ShippyPro\DpdPoland\src;

use App\Models\ShippingService;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Illuminate\Support\Str;

class ShippyProDpdPolandServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - DPD Poland Integration';

    public static string $module_description = 'Provides integration with DPD Poland';

    public static string $settings_link = '/settings/modules/couriers/dpd-poland';

    public static bool $autoEnable = false;

    protected $listen = [];

    public static function enabling(): bool
    {
        $prefixes = collect(explode(',', env('SHIPPY_PRO_DPD_POLAND_PREFIXES')));

        if (empty($prefixes)) {
            return false;
        }

        if (ShippyProApi::checkApiConnection() === false) {
            return false;
        }

        $prefixes->each(function ($prefix) {
            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefix) . '_dpd_polska_standard',
                ], [
                    'service_provider_class' => Services\DpdPolandStandardService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID_'.Str::upper($prefix)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefix) . '_dpd_polska_express',
                ], [
                    'service_provider_class' => Services\DpdPolandExpressService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID_'.Str::upper($prefix)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefix) . '_dpd_polska_odbior_w_punkcie',
                ], [
                    'service_provider_class' => Services\DpdPolandDropOffService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID_'.Str::upper($prefix)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefix) . '_dpd_polska_international',
                ], [
                    'service_provider_class' => Services\DpdPolandInternationalService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID_'.Str::upper($prefix)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefix) . '_dpd_polska_pobranie',
                ], [
                    'service_provider_class' => Services\DpdPolandCashOnDeliveryService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_DPD_POLAND_CARRIER_ID_'.Str::upper($prefix)),
                    ],
                ]);
        });
        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()
            ->whereIn('service_provider_class', [
                Services\DpdPolandStandardService::class,
                Services\DpdPolandExpressService::class,
                Services\DpdPolandDropOffService::class,
                Services\DpdPolandInternationalService::class,
                Services\DpdPolandCashOnDeliveryService::class,
            ])
            ->delete();

        return true;
    }
}
