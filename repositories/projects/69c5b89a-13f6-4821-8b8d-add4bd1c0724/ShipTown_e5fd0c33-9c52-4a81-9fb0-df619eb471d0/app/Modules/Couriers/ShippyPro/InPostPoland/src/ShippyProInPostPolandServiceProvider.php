<?php

namespace App\Modules\Couriers\ShippyPro\InPostPoland\src;

use App\Models\ShippingService;
use App\Modules\BaseModuleServiceProvider;
use App\Modules\Couriers\ShippyPro\ShippyProApi;
use Illuminate\Support\Str;

class ShippyProInPostPolandServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Courier - InPost Poland Integration';

    public static string $module_description = 'Provides integration with InPost Poland';

    public static string $settings_link = '/settings/modules/couriers/inpost-poland';

    public static bool $autoEnable = false;

    protected $listen = [];

    public static function enabling(): bool
    {
        $prefixes = collect(explode(',', env('SHIPPY_PRO_INPOST_POLAND_PREFIXES')));

        if (empty($prefixes)) {
            return false;
        }

        if (ShippyProApi::checkApiConnection() === false) {
            return false;
        }

        $prefixes->each(function ($prefixRaw) {
            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefixRaw) . '_inpost_paczkomaty_gabaryt_xs',
                ], [
                    'service_provider_class' => Services\InPostPolandLockerStandardSizeXsService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_ID_'.Str::upper($prefixRaw)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefixRaw) . '_inpost_paczkomaty_gabaryt_a',
                ], [
                    'service_provider_class' => Services\InPostPolandLockerStandardSizeAService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_ID_'.Str::upper($prefixRaw)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefixRaw) . '_inpost_paczkomaty_gabaryt_b',
                ], [
                    'service_provider_class' => Services\InPostPolandLockerStandardSizeBService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_ID_'.Str::upper($prefixRaw)),
                    ],
                ]);

            ShippingService::query()
                ->updateOrCreate([
                    'code' => Str::lower($prefixRaw) . '_inpost_paczkomaty_gabaryt_c',
                ], [
                    'service_provider_class' => Services\InPostPolandLockerStandardSizeCService::class,
                    'connection_details' => [
                        'carrier_id' => env('SHIPPY_PRO_INPOST_POLAND_CARRIER_ID_'.Str::upper($prefixRaw)),
                    ],
                ]);
        });

        return true;
    }

    public static function disabling(): bool
    {
        ShippingService::query()->whereIn('service_provider_class', [
            Services\InPostPolandLockerStandardSizeXsService::class,
            Services\InPostPolandLockerStandardSizeAService::class,
            Services\InPostPolandLockerStandardSizeBService::class,
            Services\InPostPolandLockerStandardSizeCService::class,
        ])->delete();

        return parent::disabling();
    }
}
