<?php

use App\Models\ManualRequestJob;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ManualRequestJob::query()->firstOrCreate([
            'job_class' => \App\Modules\CsvProductImports\src\Jobs\ProcessCsvProductsImportJob::class,
        ], [
            'job_name' => 'CSV Product Imports - Process CSV Products Import Job',
        ]);
    }

    public function down(): void
    {
        ManualRequestJob::query()
            ->where('job_class', \App\Modules\CsvProductImports\src\Jobs\ProcessCsvProductsImportJob::class)
            ->delete();
    }
};
