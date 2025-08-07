<?php

namespace App\Modules\DataCollectorDiscounts\src;

use App\Modules\BaseModuleServiceProvider;

class DataCollectorDiscountsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Discounts';

    public static string $module_description = 'Module provides an ability to add discounts to orders / assign discounts to customers';

    public static string $settings_link = '/settings/modules/data-collector-discounts';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];
}
