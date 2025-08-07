<?php

namespace App\Modules\Permissions\src\Listeners;

use App\Modules\Permissions\src\Jobs\CreateRoutePermissionsJob;
use Illuminate\Database\Events\NoPendingMigrations;

class NoPendingMigrationsListener
{
    public function handle(NoPendingMigrations $event): void
    {
        CreateRoutePermissionsJob::dispatch();
    }
}
