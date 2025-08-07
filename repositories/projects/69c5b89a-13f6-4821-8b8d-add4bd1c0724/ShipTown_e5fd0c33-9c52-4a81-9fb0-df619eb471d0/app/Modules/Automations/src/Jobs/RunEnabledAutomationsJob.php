<?php

namespace App\Modules\Automations\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Services\AutomationService;

class RunEnabledAutomationsJob extends UniqueJob
{
    public function handle(): void
    {
        AutomationService::runAutomationsOnOrdersQuery(
            Automation::enabled(),
            Order::placedInLast28DaysOrActive()
        );
    }
}
