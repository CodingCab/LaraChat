<?php

namespace Database\Factories\Modules\ScheduledReport\src\Models;

use App\Modules\ScheduledReport\src\Models\ScheduledReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledReportFactory extends Factory
{
    protected $model = ScheduledReport::class;

    public function definition(): array
    {
        return [
            'name' => 'Sample report scheduled',
            'uri' => '/reports/inventory-movements?filter%5Bwarehouse_code%5D=DUB&sort=-occurred_at,-sequence_number&filter%5Boccurred_at_between%5D=7%20days%20ago,now',
            'email' => 'sample@example.com',
            'cron' => '0 1 * * *', // every day at 01
            'next_run_at' => now(),
        ];
    }
}
