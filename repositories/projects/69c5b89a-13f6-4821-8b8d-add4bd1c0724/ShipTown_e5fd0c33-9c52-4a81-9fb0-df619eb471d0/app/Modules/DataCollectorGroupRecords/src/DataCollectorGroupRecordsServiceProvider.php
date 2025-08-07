<?php

namespace App\Modules\DataCollectorGroupRecords\src;

use App\Modules\BaseModuleServiceProvider;

class DataCollectorGroupRecordsServiceProvider extends BaseModuleServiceProvider
{
    public static string $module_name = 'Data Collector - Auto Group Records';

    public static string $module_description = 'Module provides an ability to group similar records in data collection';

    public static bool $autoEnable = true;

    protected $listen = [
        //
    ];
}
