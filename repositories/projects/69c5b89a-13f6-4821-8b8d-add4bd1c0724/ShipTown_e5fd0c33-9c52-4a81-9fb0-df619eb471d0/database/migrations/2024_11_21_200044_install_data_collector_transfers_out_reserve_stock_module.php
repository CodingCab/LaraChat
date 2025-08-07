<?php

use App\Modules\DataCollectorTransfersOutReserveStock\src\DataCollectorTransfersOutReserveStockServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DataCollectorTransfersOutReserveStockServiceProvider::enableModule();
    }
};
