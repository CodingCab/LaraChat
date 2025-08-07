<?php

namespace App\Modules\DataCollectorTransactions\src;

use App\Modules\BaseModuleServiceProvider;

class DataCollectorTransactionsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Transactions';

    public static string $module_description = 'Module handles various transaction related tasks';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];

    public static function enabling(): bool
    {
        return parent::enabling();
    }
}
