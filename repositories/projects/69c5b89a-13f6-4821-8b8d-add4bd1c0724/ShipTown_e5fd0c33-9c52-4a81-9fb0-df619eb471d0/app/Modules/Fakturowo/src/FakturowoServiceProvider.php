<?php

namespace App\Modules\Fakturowo\src;

use App\Models\NavigationMenu;
use App\Modules\BaseModuleServiceProvider;

/**
 * Class FakturowoServiceProvider.
 */
class FakturowoServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Fakturowo.pl Integration';

    public static string $module_description = 'Provides seamless integration with Fakturowo.pl for managing invoices.';

    public static string $settings_link = '/settings/modules/fakturowo';

    public static bool $autoEnable = false;

    public static function enabling(): bool
    {
        NavigationMenu::query()->create([
            'name' => 'Faktury Fakturowo.pl',
            'url' => '/reports/fakturowo-invoices',
            'group' => 'reports',
        ]);

        return parent::enabling();
    }

    public static function disabling(): bool
    {
        NavigationMenu::query()->where('url', '/reports/fakturowo-invoices')->delete();

        return parent::disabling();
    }
}
