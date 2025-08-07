<?php

namespace App\Modules\AssemblyProducts\src\Listeners;

use App\Modules\OrderTotals\src\Services\OrderTotalsService;
use Illuminate\Database\Events\NoPendingMigrations;

class NoPendingMigrationsListener
{
    public function handle(NoPendingMigrations $event): void
    {
        OrderTotalsService::updateTotals(41);
    }
}
