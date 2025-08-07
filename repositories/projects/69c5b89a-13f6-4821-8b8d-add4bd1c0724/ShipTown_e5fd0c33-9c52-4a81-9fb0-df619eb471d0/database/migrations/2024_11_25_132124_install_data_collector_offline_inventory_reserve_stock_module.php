<?php

use App\Modules\DataCollectorOfflineInventoryReserveStock\src\DataCollectorOfflineInventoryReserveStockServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DataCollectorOfflineInventoryReserveStockServiceProvider::enableModule();
    }
};
