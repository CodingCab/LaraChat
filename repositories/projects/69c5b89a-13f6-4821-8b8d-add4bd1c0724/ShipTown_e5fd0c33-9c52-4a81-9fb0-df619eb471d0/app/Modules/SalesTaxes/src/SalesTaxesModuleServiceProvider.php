<?php

namespace App\Modules\SalesTaxes\src;

use App\Modules\BaseModuleServiceProvider;

class SalesTaxesModuleServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Sales Taxes';

    public static string $module_description = 'Module provides an ability to add sales taxes to the products.';

    public static string $settings_link = '/settings/modules/sales-taxes';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
