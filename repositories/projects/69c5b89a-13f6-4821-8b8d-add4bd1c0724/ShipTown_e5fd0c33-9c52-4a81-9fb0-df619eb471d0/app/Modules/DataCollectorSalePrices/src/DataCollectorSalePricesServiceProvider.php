<?php

namespace App\Modules\DataCollectorSalePrices\src;

use App\Modules\BaseModuleServiceProvider;

class DataCollectorSalePricesServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Sale Prices';

    public static string $module_description = 'Module applies sale prices to transaction records';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
