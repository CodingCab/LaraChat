<?php

use App\Modules\DataCollectorSalePrices\src\DataCollectorSalePricesServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DataCollectorSalePricesServiceProvider::installModule();
    }
};
