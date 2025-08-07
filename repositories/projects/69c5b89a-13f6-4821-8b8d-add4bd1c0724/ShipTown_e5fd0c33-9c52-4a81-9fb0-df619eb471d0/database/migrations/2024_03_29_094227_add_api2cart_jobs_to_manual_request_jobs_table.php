<?php

use App\Models\ManualRequestJob;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ManualRequestJob::query()->create([
            'job_name' => 'Api2cart - Dispatch Import Orders Jobs',
            'job_class' => \App\Modules\Api2cart\src\Jobs\DispatchImportOrdersJobs::class,
        ]);
    }
};
