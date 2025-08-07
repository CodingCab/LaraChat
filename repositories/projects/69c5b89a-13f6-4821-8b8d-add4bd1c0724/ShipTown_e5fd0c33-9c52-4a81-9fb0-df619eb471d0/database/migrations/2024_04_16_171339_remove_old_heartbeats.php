<?php

use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryMonthEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Jobs\DispatchEveryWeekEventJob;
use App\Models\Heartbeat;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Heartbeat::where('code', DispatchEveryMinuteEventJob::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryFiveMinutesEventJob::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryTenMinutesEventJob::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryHourEventJobs::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryDayEventJob::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryWeekEventJob::class)->forceDelete();
        Heartbeat::where('code', DispatchEveryMonthEventJob::class)->forceDelete();
    }
};
