<?php

use App\Modules\SalesTaxes\src\SalesTaxesModuleServiceProvider;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        SalesTaxesModuleServiceProvider::installModule();
    }
};
