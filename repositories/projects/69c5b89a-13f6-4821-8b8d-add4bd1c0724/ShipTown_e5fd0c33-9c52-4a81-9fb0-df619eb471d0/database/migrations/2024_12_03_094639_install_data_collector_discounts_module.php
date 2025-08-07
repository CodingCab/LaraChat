<?php

use App\Modules\DataCollectorDiscounts\src\DataCollectorDiscountsServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DataCollectorDiscountsServiceProvider::enableModule();
    }
};
