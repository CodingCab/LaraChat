<?php

use App\Modules\DataCollectorGroupRecords\src\DataCollectorGroupRecordsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DataCollectorGroupRecordsServiceProvider::installModule();
    }
};
