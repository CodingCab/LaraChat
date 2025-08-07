<?php

namespace App\Modules\PointOfSaleConfiguration\src;

use App\Modules\BaseModuleServiceProvider;
use App\Modules\PointOfSaleConfiguration\src\Models\PointOfSaleConfiguration;

class PointOfSaleConfigurationServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Point Of Sale Configuration';

    public static string $module_description = 'Provides an ability to configure the point of sale functionality';

    public static string $settings_link = '/settings/modules/point-of-sale-configuration';

    public static bool $autoEnable = true;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        //
    ];

    public static function enabling(): bool
    {
        PointOfSaleConfiguration::query()->updateOrCreate([
            'next_transaction_number' => 1,
        ]);

        return true;
    }
}
