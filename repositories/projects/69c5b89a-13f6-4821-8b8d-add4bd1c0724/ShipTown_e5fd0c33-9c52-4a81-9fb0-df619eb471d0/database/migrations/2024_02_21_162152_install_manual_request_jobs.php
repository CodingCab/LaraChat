<?php

use App\Models\ManualRequestJob;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ManualRequestJob::query()->create([
            'job_name' => 'Core - Dispatch Every Minute Event Job',
            'job_class' => App\Jobs\DispatchEveryMinuteEventJob::class,
        ]);

        ManualRequestJob::query()->create([
            'job_name' => 'Core - Dispatch Every Five Minutes Event Job',
            'job_class' => App\Jobs\DispatchEveryFiveMinutesEventJob::class,
        ]);

        ManualRequestJob::query()->create([
            'job_name' => 'Core - Dispatch Every Ten Minutes Event Job',
            'job_class' => App\Jobs\DispatchEveryTenMinutesEventJob::class,
        ]);

        ManualRequestJob::query()->create([
            'job_name' => 'Core - Dispatch Every Hour Event Job',
            'job_class' => App\Jobs\DispatchEveryHourEventJobs::class,
        ]);

        ManualRequestJob::query()->create([
            'job_name' => 'Core - Dispatch Every Day Event Job',
            'job_class' => App\Jobs\DispatchEveryDayEventJob::class,
        ]);
    }
};
