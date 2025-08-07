<?php

use App\Modules\InventoryMovementsDailyStatistics\src\InventoryMovementsDailyStatisticsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        InventoryMovementsDailyStatisticsServiceProvider::installModule();
    }
};
