<?php

namespace App\Modules\Automations\src\Jobs;

use App\Abstracts\UniqueJob;
use App\Models\Order;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Services\AutomationService;

class RunEnabledAutomationsOnSpecificOrderJob extends UniqueJob
{
    private int $order_id;

    public int $uniqueFor = 60;

    public function uniqueId(): string
    {
        return implode('-', [get_class($this), $this->order_id]);
    }

    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    public function handle(): void
    {
        AutomationService::runAutomationsOnOrdersQuery(
            Automation::enabled(),
            Order::placedInLast28DaysOrActive()->where(['id' => $this->order_id])
        );
    }
}
