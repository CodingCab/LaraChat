<?php

namespace App\Modules\DataCollectorQuantityDiscounts\src;

use App\Modules\BaseModuleServiceProvider;

class QuantityDiscountsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Quantity Discounts';

    public static string $module_description = 'Module provides an ability to use quantity discounts for price calculation.';

    public static string $settings_link = '/settings/modules/quantity-discounts';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
