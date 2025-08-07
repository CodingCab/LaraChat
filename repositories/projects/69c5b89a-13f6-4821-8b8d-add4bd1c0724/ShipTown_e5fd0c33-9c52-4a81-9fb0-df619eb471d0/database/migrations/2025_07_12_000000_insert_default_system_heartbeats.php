<?php

use App\Models\Heartbeat;
use App\Jobs\DispatchEveryMinuteEventJob;
use App\Jobs\DispatchEveryFiveMinutesEventJob;
use App\Jobs\DispatchEveryTenMinutesEventJob;
use App\Jobs\DispatchEveryHourEventJobs;
use App\Jobs\DispatchEveryDayEventJob;
use App\Jobs\DispatchEveryWeekEventJob;
use App\Jobs\DispatchEveryMonthEventJob;
use App\Modules\SystemHeartbeats\src\Listeners\EveryMinuteEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryFiveMinutesEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryTenMinutesEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryHourEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryDayEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryWeekEventListener;
use App\Modules\SystemHeartbeats\src\Listeners\EveryMonthEventListener;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            EveryMinuteEventListener::class => [
                'error_message' => 'Every Minute heartbeat missed',
                'expires_at' => now()->subMinute(),
                'auto_heal_job_class' => DispatchEveryMinuteEventJob::class,
            ],
            EveryFiveMinutesEventListener::class => [
                'error_message' => 'Every Five minutes heartbeat missed',
                'expires_at' => now()->subMinutes(5),
                'auto_heal_job_class' => DispatchEveryFiveMinutesEventJob::class,
            ],
            EveryTenMinutesEventListener::class => [
                'error_message' => 'Every Ten Minutes heartbeat missed',
                'expires_at' => now()->subMinutes(10),
                'auto_heal_job_class' => DispatchEveryTenMinutesEventJob::class,
            ],
            EveryHourEventListener::class => [
                'error_message' => 'Every Hour Event heartbeat missed',
                'expires_at' => now()->subHour(),
                'auto_heal_job_class' => DispatchEveryHourEventJobs::class,
            ],
            EveryDayEventListener::class => [
                'error_message' => 'Every Day heartbeat missed',
                'expires_at' => now()->subDay(),
                'auto_heal_job_class' => DispatchEveryDayEventJob::class,
            ],
            EveryWeekEventListener::class => [
                'error_message' => 'Every Week heartbeat missed',
                'expires_at' => now()->subWeek(),
                'auto_heal_job_class' => DispatchEveryWeekEventJob::class,
            ],
            EveryMonthEventListener::class => [
                'error_message' => 'Every Month heartbeat missed',
                'expires_at' => now()->subMonth(),
                'auto_heal_job_class' => DispatchEveryMonthEventJob::class,
            ],
        ];

        foreach ($defaults as $code => $values) {
            Heartbeat::query()->updateOrCreate(
                ['code' => $code],
                [
                    'error_message' => $values['error_message'],
                    'expires_at' => $values['expires_at'],
                    'auto_heal_job_class' => $values['auto_heal_job_class'],
                ]
            );
        }
    }
};
