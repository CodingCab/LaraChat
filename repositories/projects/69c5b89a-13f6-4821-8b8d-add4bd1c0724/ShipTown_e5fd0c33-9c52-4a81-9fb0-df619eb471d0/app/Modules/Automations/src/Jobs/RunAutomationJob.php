<?php

namespace App\Modules\Automations\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Services\AutomationService;

class RunAutomationJob extends UniqueJob
{
    private int $automation_id;

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->automation_id]);
    }

    public function __construct(int $automation_id)
    {
        $this->automation_id = $automation_id;
    }

    public function handle(): void
    {
        AutomationService::runAutomationsOnOrdersQuery(
            Automation::whereId($this->automation_id),
            Order::placedInLast28DaysOrActive()
        );
    }
}
