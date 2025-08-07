<?php

namespace App\Modules\Maintenance\src\Listeners\DailyEvent;

use App\Models\Heartbeat;
use Carbon\Carbon;

class CheckLicenseExpirationListener
{
    public function handle(): void
    {
        $license_valid_until = Carbon::createFromTimeString(config('app.license_valid_until', '2025-06-01 00:00:00'));

        Heartbeat::query()->updateOrCreate([
            'code' => 'license_expiration',
        ], [
            'error_message' => 'Your license is about to expire, please contact your administrator',
            'expires_at' => $license_valid_until,
        ]);
    }
}
