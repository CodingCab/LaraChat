<?php

namespace App\Modules\Permissions\src\Listeners;

use App\Modules\Permissions\src\Jobs\CreateRoutePermissionsJob;
use Illuminate\Database\Events\MigrationsEnded;

class MigrationsEndedListener
{
    public function handle(MigrationsEnded $event): void
    {
        CreateRoutePermissionsJob::dispatch();
    }
}
