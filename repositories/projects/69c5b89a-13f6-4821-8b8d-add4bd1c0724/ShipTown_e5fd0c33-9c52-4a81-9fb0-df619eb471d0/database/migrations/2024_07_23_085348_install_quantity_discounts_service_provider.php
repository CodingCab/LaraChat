<?php

use App\Modules\DataCollectorQuantityDiscounts\src\QuantityDiscountsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        QuantityDiscountsServiceProvider::installModule();
    }
};
